<?php

/**
 * @file ItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use TooBasic\Managers\DBManager;

/**
 * @class ItemRepresentation
 * @abstract
 * This is an abstract representation of a database item representation. In a
 * completely abstract point of view, this is the representation of a database
 * table row and all its fields.
 */
abstract class ItemRepresentation {
	//
	// Protected core properties.
	/**
	 * @var string Generic prefix for all columns on the represented table.
	 */
	protected $_CP_ColumnsPerfix = '';
	/**
	 * @var string Name of a field containing IDs (without prefix).
	 */
	protected $_CP_IDColumn = '';
	/**
	 * @var string Name of a field containing names (without prefix).
	 */
	protected $_CP_NameColumn = 'name';
	/**
	 * @var string[] List of fields that can't be alter by generic accessors.
	 */
	protected $_CP_ReadOnlyColumns = array();
	/**
	 * @var string Represented table's name (without prefix).
	 */
	protected $_CP_Table = '';
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter Database connection shortcut
	 */
	protected $_db = false;
	/**
	 * @var string Database connection name shortcut.
	 */
	protected $_dbprefix = '';
	/**
	 * @var bool This flag indicates if a property has been modified and not
	 * yet store on database.
	 */
	protected $_dirty = false;
	/**
	 *
	 * @var bool This flag is true when the current object represent a actual
	 * entry on database.
	 */
	protected $_exists = false;
	/**
	 * @var mixed[string] List of properties stored in this object that does
	 * not represent real field on database. These can also be called volatile
	 * properties.
	 */
	protected $_extraProperties = array();
	/**
	 * @var string[] Last database error detected.
	 */
	protected $_lastDBError = false;
	protected $_properties = array();
	/**
	 * @var string[string] List of prefixes used by query adapters.
	 */
	protected $_queryAdapterPrefixes = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $dbname Database connection name to which this
	 * representation is associated.
	 */
	public function __construct($dbname) {
		//
		// Generating shortcuts.
		$this->_db = DBManager::Instance()->{$dbname};
		$this->_dbprefix = $this->_db->prefix();
	}
	/**
	 * This magic method allows to directly print a representation.
	 *
	 * @return string Pretty formatted string with basic information of this
	 * representation.
	 */
	public function __toString() {
		return $this->exists() ? get_called_class().'[('.$this->{$this->_CP_IDColumn}.')]' : 'NULL';
	}
	/**
	 * This magic method provides a quick access to field values. If the
	 * requested properties in not a known table column, it will be looked for
	 * among extra properties.
	 *
	 * @param string $name Property name to look for.
	 * @return mixed Returns the value of the request property or FALSE if not
	 * found.
	 */
	public function __get($name) {
		//
		// Default values.
		$out = null;
		//
		// Generating a possible table field name.
		$realName = "{$this->_CP_ColumnsPerfix}{$name}";
		//
		// Attepting to obtain a value either from knwon fields or from
		// extra properties.
		if(array_key_exists($realName, $this->_properties)) {
			$out = $this->_properties[$realName];
		} elseif(array_key_exists($name, $this->_extraProperties)) {
			$out = $this->_extraProperties[$name];
		}

		return $out;
	}
	/**
	 * This magic method allows to set a value to certain property in this
	 * representation. If the property is a known table column it could be use
	 * later to modify the database, if not, an extra property will be alter.
	 *
	 * @param string $name Property name to look for.
	 * @param mixed $value value to assign.
	 * @return mixed Returns the set value.
	 */
	public function __set($name, $value) {
		//
		// Generating a possible table field name.
		$realName = "{$this->_CP_ColumnsPerfix}{$name}";
		//
		// Checking if its a known table column.
		if(array_key_exists($realName, $this->_properties)) {
			//
			// Checking that:
			//	- the column is not the ID column.
			//	- It's not a read only property.
			//	- The value is different from the current one.
			if($name != $this->_CP_IDColumn && !in_array($name, $this->_CP_ReadOnlyColumns) && $this->_properties[$realName] != $value) {
				//
				// Setting a new value.
				$this->_properties[$realName] = $value;
				//
				// Flagging this representation as dirty.
				$this->_dirty = true;
			} else {
				//
				// When the requeried conditions fail, this
				// returned value must be the one that remains on
				// the column.
				$value = $this->_properties[$realName];
			}
		} else {
			//
			// Storing the property as an extra property.
			$this->_extraProperties[$name] = $value;
		}

		return $value;
	}
	//
	// Public methods.
	/**
	 * This method indicates if this representation has unsaved data.
	 *
	 * @return bool Returns a data status.
	 */
	public function dirty() {
		return $this->_dirty;
	}
	/**
	 * This method indicates if this object represent a existing row on a
	 * table.
	 *
	 * @return bool Returns a existence status.
	 */
	public function exists() {
		return $this->_exists;
	}
	/**
	 * This method provids access to the last database error found.
	 *
	 * @return string[] Returns a database error information.
	 */
	public function lastDBError() {
		return $this->_lastDBError;
	}
	/**
	 * This method attempts to load this object with information from a row on
	 * database.
	 *
	 * @param int $id Database identifier of the row to reprenset.
	 * @return bool Returns a existence status.
	 */
	public function load($id) {
		//
		// Cleaning previous loaded data (in case of reuse).
		$this->reset();
		//
		// Triggering specific pre-loading operations.
		$this->preLoad($id);
		//
		// Default values.
		$data = false;
		//
		// Generating a proper query.
		$query = $this->_db->queryAdapter()->select($this->_CP_Table, array($this->_CP_IDColumn => $id), $this->queryAdapterPrefixes());
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Retrieving information.
		if(!$stmt->execute($query[GC_AFIELD_PARAMS])) {
			$this->_lastDBError = $stmt->errorInfo();
		} else {
			$data = $stmt->fetch();
		}
		//
		// Checking if something was found or not.
		if($data) {
			//
			// Found fields go directly as proeprties.
			$this->_properties = $data;
			//
			// At this point, the entry exists.
			$this->_exists = true;
			//
			// Triggering specific post-loading operations.
			$this->postLoad();
		} else {
			$this->_exists = false;
		}
		//
		// Returning the final existence status.
		return $this->exists();
	}
	/**
	 * This method attempts to load this object with information from a row on
	 * database based on a name instead of an id.
	 * In the case more than one row has the same name, only the first found
	 * will be taken as valid.
	 *
	 * @param string $name Name to look for.
	 * @return bool Returns a existence status.
	 * @throws \TooBasic\DBException
	 */
	public function loadByName($name) {
		//
		// Cleaning previous loaded data (in case of reuse).
		$this->reset();
		//
		// Checking that there's a name column configured, otherwise, this
		// would represent a fatal error.
		if($this->_CP_NameColumn) {
			//
			// Generating a proper query.
			$query = $this->_db->queryAdapter()->select($this->_CP_Table, array($this->_CP_NameColumn => $name), $this->queryAdapterPrefixes());
			$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
			//
			// Retrieving information based on a name.
			if($stmt->execute($query[GC_AFIELD_PARAMS])) {
				$row = $stmt->fetch();
				//
				// Checking if something was actually found.
				if($row) {
					//
					// Forwarding the operation to the proper
					// method.
					$idKey = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
					$this->load($row[$idKey]);
				}
			} else {
				$this->_lastDBError = $stmt->errorInfo();
			}
		} else {
			throw new \TooBasic\DBException("No name column set for table '{$this->_CP_Table}'");
		}

		return $this->exists();
	}
	/**
	 * This method allows to store data modification on database.
	 *
	 * @return bool Returns TRUE when the database entry was successfully
	 * updated.
	 */
	public function persist() {
		//
		// Default values.
		$persisted = false;
		//
		// Checking that there's something to persist and also triggering
		// specific checks before persisting.
		if($this->dirty() && $this->prePersist()) {
			$idName = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
			//
			// Building the list of values to store associated to
			// their column names.
			$data = array();
			foreach($this->_properties as $key => $value) {
				$shortKey = substr($key, strlen($this->_CP_ColumnsPerfix));
				if($idName != $key && !in_array($shortKey, $this->_CP_ReadOnlyColumns)) {
					$data[$shortKey] = $value;
				}
			}
			//
			// Generating the proper query to update the entry.
			$query = $this->_db->queryAdapter()->update($this->_CP_Table, $data, array($this->_CP_IDColumn => $this->id), $this->queryAdapterPrefixes());
			$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
			//
			// Attemptting to update.
			if($stmt->execute($query[GC_AFIELD_PARAMS])) {
				//
				// At this point it's considered to be persisted
				// and no longer dirty.
				$persisted = true;
				$this->_dirty = false;
			} else {
				$this->_lastDBError = $stmt->errorInfo();
			}
		}

		return $persisted;
	}
	/**
	 * This method provides a way to remove a represented entry from database.
	 *
	 * @return bool Returns a non-existence status.
	 */
	public function remove() {
		//
		// Generating a proper query to erase an entry based on its id.
		$query = $this->_db->queryAdapter()->delete($this->_CP_Table, array($this->_CP_IDColumn => $this->id), $this->queryAdapterPrefixes());
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Attemptting to remove it.
		if(!$stmt->execute($query[GC_AFIELD_PARAMS])) {
			$this->_lastDBError = $stmt->errorInfo();
		}
		//
		// Reloading this representation to ensure it was remove.
		$this->load($this->id);
		//
		// If everything went according to plan, this representation
		// should not exist.
		return !$this->exists();
	}
	/**
	 * This method clear internal properties allowing this object to be
	 * reused.
	 */
	public function reset() {
		$this->_dirty = false;
		$this->_exists = false;
		$this->_extraProperties = array();
		$this->_properties = array();
	}
	/**
	 * This method allows to access this representation as simple array.
	 * Useful to export this object into a view.
	 *
	 * @return mixed[string] List of field names associated to their values.
	 */
	public function toArray() {
		$out = $this->_extraProperties;

		foreach($this->_properties as $key => $value) {
			$out[substr($key, strlen($this->_CP_ColumnsPerfix))] = $value;
		}

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method is an entry point for sub-classes to perform specific
	 * operations before an item is loaded.
	 *
	 * @param int $id Row identifier to be loaded.
	 */
	protected function preLoad($id) {
		//
		// Sub-class responsibility.
	}
	/**
	 * This method is an entry point for sub-classes to perform specific
	 * operations after an item is successfully loaded.
	 */
	protected function postLoad() {
		//
		// Sub-class responsibility.
	}
	/**
	 * This method is an entry point for sub-classes to perform specific
	 * checks before an item is persisted on database.
	 *
	 * @return bool It must return TRUE when it is ok to persist current
	 * information into the database.
	 */
	protected function prePersist() {
		//
		// Sub-class responsibility.
		return true;
	}
	/**
	 * This method is an entry point for sub-classes to perform specific
	 * operations after an item is persisted on database.
	 */
	protected function postPersist() {
		//
		// Sub-class responsibility.
	}
	/**
	 * This method generates and returns a list of prefixes required by query
	 * adapetrs.
	 *
	 * @return string[string] List of prefixes.
	 */
	protected function queryAdapterPrefixes() {
		if($this->_queryAdapterPrefixes === false) {
			$this->_queryAdapterPrefixes = array(
				GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
				GC_DBQUERY_PREFIX_COLUMN => $this->_CP_ColumnsPerfix
			);
		}
		return $this->_queryAdapterPrefixes;
	}
}
