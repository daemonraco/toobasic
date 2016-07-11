<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Managers\DBManager;
use TooBasic\Names;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class ItemsFactory
 * @abstract
 * This is an abstract representation of a database item representation factory.
 * In a completely abstract point of view, this is the representation of a
 * database table and all its entries.
 */
abstract class ItemsFactory {
	//
	// Protected class properties.
	/**
	 * @var string[string] List of loaded representation classes.
	 */
	protected static $_LoadedClasses = array();
	//
	// Protected core properties.
	/**
	 * @var string Generic prefix for all columns on the represented table.
	 */
	protected $_CP_ColumnsPerfix = '';
	/**
	 * @var boolean This flag indicates if method 'create()' is disabled or
	 * not.
	 * It can also have a string as value and it will be used as method name
	 * when its related exception is raised.
	 */
	protected $_CP_DisableCreate = false;
	/**
	 * @var string Name of a field containing IDs (without prefix).
	 */
	protected $_CP_IDColumn = '';
	/**
	 * @var string Name of a field containing names (without prefix).
	 */
	protected $_CP_NameColumn = 'name';
	/**
	 * @var string[string] List of fields (without prefix) associated to a
	 * sorting direction.
	 */
	protected $_CP_OrderBy = false;
	/**
	 * @var string Name of a \TooBasic\Representations\ItemRepresentation
	 * class.
	 */
	protected $_CP_RepresentationClass = '';
	/**
	 * @var string Represented table's name (without prefix).
	 */
	protected $_CP_Table = '';
	//
	// Protected properties.	
	/**
	 * @var \TooBasic\Adapters\DB\Adapter Database connection shortcut.
	 */
	protected $_db = false;
	/**
	 * @var string Database connection name shortcut.
	 */
	protected $_dbname = false;
	/**
	 * @var string Tables' prefix shortcut.
	 */
	protected $_dbprefix = '';
	/**
	 * @var string[] Last database error detected.
	 */
	protected $_lastDBError = false;
	/**
	 * @var string[string] List of prefixes used by query adapters.
	 */
	protected $_queryAdapterPrefixes = false;
	//
	// Magic methods.
	/**
	 * Prevent users from directly creating the singleton's instance.
	 */
	final protected function __constructor() {
		//
		// Checking if there's an ID field configured, if not it means
		// this representation doesn't support empty entries creation.
		if(!$this->_CP_IDColumn) {
			$this->_CP_DisableCreate = true;
		}
	}
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		throw new Exception(Translate::Instance()->EX_obj_clone_forbidden);
	}
	//
	// Public methods.
	/**
	 * This method allows to create an empty entry for the represented table.
	 *
	 * @warning If any field on a represented table has no defualt value this
	 * method may cause errors.
	 *
	 * @return int Returns the ID of the new entry or false when it wasn't 
	 * @throws \TooBasic\Exception
	 */
	public function create() {
		//
		// Checking if this method is disabled.
		if(\boolval($this->_CP_DisableCreate)) {
			if(is_string($this->_CP_DisableCreate)) {
				throw new Exception("Method 'create()' cannot be called directly. Use '{$this->_CP_DisableCreate}()' instead.");
			} else {
				throw new Exception("Method 'create()' cannot be called directly.");
			}
		}
		//
		// Default values.
		$out = false;
		//
		// Generating a proper query to insert an empty entry.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->createEmptyEntry($this->_CP_Table, array(
			GC_DBQUERY_NAMES_COLUMN_ID => $this->_CP_IDColumn,
			GC_DBQUERY_NAMES_COLUMN_NAME => $this->_CP_NameColumn
			), $prefixes);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Attepting to create an entry.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			//
			// Fetching the ID.
			$out = $this->_db->lastInsertId($query[GC_AFIELD_SEQNAME]);
		} else {
			//
			// Catching the last error for further analysis.
			$this->_lastDBError = $stmt->errorInfo();
		}

		return $out;
	}
	/**
	 * This method retrieves a list of ID of all available entries in the
	 * represented table.
	 *
	 * @return int[] Returns a list of IDs.
	 */
	public function ids() {
		//
		// Default values.
		$out = array();
		//
		// Enforcing order configuration.
		if(!is_array($this->_CP_OrderBy)) {
			$this->_CP_OrderBy = array();
		}
		//
		// Generating a proper query to obtain a full list of IDs.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->select($this->_CP_Table, array(), $prefixes, $this->_CP_OrderBy);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Executing query and fetching IDs.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$idKey = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
			foreach($stmt->fetchAll() as $row) {
				$out[] = $row[$idKey];
			}
		} else {
			//
			// Catching the last error for further analysis.
			$this->_lastDBError = $stmt->errorInfo();
		}

		return $out;
	}
	/**
	 * This method retrieves a list of ID of all available entries where the
	 * column flagged as name follows certain pattern.
	 *
	 * @return int[] Returns a list of IDs.
	 */
	public function idsByNamesLike($pattern) {
		//
		// Checking if there's an name field configured, if not it means
		// this representation doesn't support this method.
		if(!$this->_CP_NameColumn) {
			throw new Exception('This representation has no name field defined meaning it does not support this method');
		}
		//
		// Default values.
		$out = array();
		//
		// Enforcing order configuration.
		if(!is_array($this->_CP_OrderBy)) {
			$this->_CP_OrderBy = array();
		}
		//
		// Generating a proper query to obtain a list of IDs.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->select($this->_CP_Table, array("*:{$this->_CP_NameColumn}" => $pattern), $prefixes, $this->_CP_OrderBy);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Executing query and fetching IDs.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$idKey = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
			foreach($stmt->fetchAll() as $row) {
				$out[] = $row[$idKey];
			}
		} else {
			//
			// Catching the last error for further analysis.
			$this->_lastDBError = $stmt->errorInfo();
		}

		return $out;
	}
	/**
	 * This method allows to obtain a representation for certain item based on
	 * its ID.
	 *
	 * @param int $id Id to look for.
	 * @return \TooBasic\Representations\ItemRepresentation Returns a
	 * representation when found or NULL if not.
	 * @throws \TooBasic\Exception
	 */
	public function item($id) {
		//
		// Checking if there's an ID field configured, if not it means
		// this representation doesn't support this method.
		if(!$this->_CP_IDColumn) {
			throw new Exception('This representation has no ID field defined meaning it does not support this method');
		}
		//
		// Obtaining an object to hold the represented entry.
		$item = self::GetClass($this->_CP_RepresentationClass, $this->_dbname);
		//
		// Attempting to load the information based on the id.
		$item->load($id);
		//
		// Was it found?
		if(!$item->exists()) {
			$item = null;
		}

		return $item;
	}
	/**
	 * This method allows to obtain a representation for certain item based on
	 * its name.
	 *
	 * @param type $name Name to look for.
	 * @return \TooBasic\Representations\ItemRepresentation Returns a
	 * representation when found or NULL if not.
	 * @throws \TooBasic\Exception
	 */
	public function itemByName($name) {
		//
		// Checking if there's an name field configured, if not it means
		// this representation doesn't support this method.
		if(!$this->_CP_NameColumn) {
			throw new Exception('This representation has no name field defined meaning it does not support this method');
		}
		//
		// Obtaining an object to hold the represented entry.
		$item = self::GetClass($this->_CP_RepresentationClass, $this->_dbname);
		//
		// Attempting to load the information based on the id.
		$item->loadByName($name);
		//
		// Was it found?
		if(!$item->exists()) {
			$item = null;
		}

		return $item;
	}
	/**
	 * This method retrieves a list of representations of all available
	 * entries in the represented table.
	 *
	 * @return \TooBasic\Representations\ItemRepresentation[] Returns a list
	 * of representations.
	 */
	public function items() {
		//
		// Default values.
		$out = array();
		//
		// Fetching all available dis and creating a representation for
		// each one of them.
		foreach($this->ids() as $id) {
			$out[$id] = $this->item($id);
		}

		return $out;
	}
	/**
	 * This method provids access to the last database error found.
	 *
	 * @return string[] Returns a database error information.
	 */
	public function lastDBError() {
		return $this->_lastDBError;
	}
	//
	// Protected methods.
	/**
	 * Singleton initializer
	 */
	protected function init() {
		//
		// Creating a shortcuts.
		$this->_db = DBManager::Instance()->{$this->_dbname};
		$this->_dbprefix = $this->_db->prefix();
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
	//
	// Public class methods.
	/**
	 * This method provides access to singleton instances of representation
	 * factories.
	 *
	 * @param string $dbname Database connection name associated with the
	 * requested singleton instance.
	 * @return \TooBasic\Representations\ItemsFactory Returns a representation
	 * factory instance.
	 */
	final public static function &Instance($dbname = false) {
		//
		// List of loaded singleton instances.
		static $Instances = array();
		//
		// When no database name is specified, the defualt must be used.
		if($dbname === false) {
			$dbname = DBManager::Instance()->getDefaultName();
		}

		$classname = get_called_class();
		//
		// If there's not an instance created for the requested factory
		// and database name it must be created.
		if(!isset($Instances[$classname][$dbname])) {
			//
			// Generaring a list for each factory in which store
			// instances associated to their database connection name.
			if(!isset($Instances[$classname])) {
				$Instances[$classname] = array();
			}
			//
			// Generating and initializing the instance.
			$Instances[$classname][$dbname] = new $classname();
			$Instances[$classname][$dbname]->_dbname = $dbname;
			$Instances[$classname][$dbname]->init();
		}
		//
		// Returning the requeste instance.
		return $Instances[$classname][$dbname];
	}
	//
	// Protected class methods.
	/**
	 * This method is the one in charge of searching, loading and keeping
	 * track of all requested representation classes.
	 *
	 * @param string $name Name to use as search pattern for a representaion.
	 * It may be prefixed with a namespace.
	 * @param string $dbname Database connection name to use on the returned
	 * representation.
	 * @return string Returns the requested representation's class.
	 * @throws \TooBasic\Exception
	 */
	protected static function GetClass($name, $dbname) {
		//
		// Guessing class name.
		$className = Names::ItemRepresentationClass($name);
		//
		// If it was never loaded, it should give it a try, otherwise, an
		// internal list is used.
		if(!in_array($className, self::$_LoadedClasses)) {
			//
			// Checking if the class was loaded by an external
			// mechanism.
			if(class_exists($className)) {
				//
				// Setting class as loaded.
				self::$_LoadedClasses[] = $className;
			} else {
				//
				// Guessing class name.
				$filename = Paths::Instance()->representationPath(Names::ItemRepresentationFilename($name));
				//
				// Checking the file path existence.
				if($filename) {
					//
					// Loading the file
					require_once $filename;
					//
					// Checking if the class was successfully
					// loaded.
					if(class_exists($className)) {
						//
						// Setting class as loaded.
						self::$_LoadedClasses[] = $className;
					} else {
						throw new Exception(Translate::Instance()->EX_undefined_class(['name' => $className]));
					}
				} else {
					throw new Exception(Translate::Instance()->EX_cannot_load_representation_class(['name' => $className]));
				}
			}
		}
		//
		// Returning the found class for the right database connection.
		return new $className($dbname);
	}
}
