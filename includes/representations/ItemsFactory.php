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
use TooBasic\Representations\CoreProps;
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
	protected static $_LoadedClasses = [];
	//
	// Protected properties.	
	/**
	 * @var string Name of the class or JSON specs where core properties are
	 * held.
	 */
	protected $_corePropsHolder = false;
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
	final protected function __construct() {
		//
		// Checking core properties holder name.
		if(!$this->_corePropsHolder) {
			throw new Exception(Translate::Instance()->EX_no_core_props_holder);
		}
		//
		// Checking if there's an ID field configured, if not it means
		// this representation doesn't support empty entries creation.
		if(!$this->_cp_IDColumn) {
			$this->_cp_DisableCreate = true;
		}
	}
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		throw new Exception(Translate::Instance()->EX_obj_clone_forbidden);
	}
	/**
	 * This magic method provides a quick access to core properties.
	 *
	 * @param string $name Property name to look for.
	 * @return mixed Returns the value of the request property or FALSE if not
	 * found.
	 */
	public function __get($name) {
		//
		// Default values.
		$out = false;
		//
		// Checking if it's core property request
		if(preg_match('~^_(cp|CP)_(?<name>.*)$~', $name, $match)) {
			$out = CoreProps::GetCoreProps($this->_corePropsHolder)->{$match['name']};
		}

		return $out;
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
		if(\boolval($this->_cp_DisableCreate)) {
			if(is_string($this->_cp_DisableCreate)) {
				throw new Exception(Translate::Instance()->EX_create_cannot_be_called_use(['method' => $this->_cp_DisableCreate]));
			} else {
				throw new Exception(Translate::Instance()->EX_create_cannot_be_called);
			}
		}
		//
		// Default values.
		$out = false;
		//
		// Generating a proper query to insert an empty entry.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->createEmptyEntry($this->_cp_Table, [
			GC_DBQUERY_NAMES_COLUMN_ID => $this->_cp_IDColumn,
			GC_DBQUERY_NAMES_COLUMN_NAME => $this->_cp_NameColumn
			], $prefixes);
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
		$out = [];
		//
		// Enforcing order configuration.
		if(!is_array($this->_cp_OrderBy)) {
			$this->_cp_OrderBy = [];
		}
		//
		// Generating a proper query to obtain a full list of IDs.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->select($this->_cp_Table, [], $prefixes, $this->_cp_OrderBy);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Executing query and fetching IDs.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$idKey = "{$this->_cp_ColumnsPerfix}{$this->_cp_IDColumn}";
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
	 * This method retrieves a list of IDs of all available entries where the
	 * column flagged as name follows certain pattern.
	 *
	 * @return int[] Returns a list of IDs.
	 */
	public function idsByNamesLike($pattern) {
		//
		// Checking if there's an name field configured, if not it means
		// this representation doesn't support this method.
		if(!$this->_cp_NameColumn) {
			throw new Exception(Translate::Instance()->EX_representation_has_no_name_field_defined);
		}
		//
		// Default values.
		$out = [];
		//
		// Enforcing order configuration.
		if(!is_array($this->_cp_OrderBy)) {
			$this->_cp_OrderBy = [];
		}
		//
		// Generating a proper query to obtain a list of IDs.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->select($this->_cp_Table, ["*:{$this->_cp_NameColumn}" => $pattern], $prefixes, $this->_cp_OrderBy);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Executing query and fetching IDs.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$idKey = "{$this->_cp_ColumnsPerfix}{$this->_cp_IDColumn}";
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
	 * This method retrieves a list of IDs in the represented table based on a
	 * set of conditions.
	 *
	 * Conditions is an associative array where keys are field names without
	 * prefixes and associated values are values to look for.
	 *
	 * @param mixed[string] $conditions List of conditions to apply.
	 * @param string[string] $order Query sorting conditions.
	 * @return int[] Returns a list of IDs.
	 */
	public function idsBy($conditions, $order = []) {
		//
		// Default values.
		$out = [];
		//
		// Checking conditions.
		if(!is_array($conditions)) {
			throw new Exception(Translate::Instance()->EX_parameter_is_not_instances_of([
				'name', '$conditions',
				'type' => 'array'
			]));
		}
		//
		// Checking order.
		if(!is_array($order)) {
			throw new Exception(Translate::Instance()->EX_parameter_is_not_instances_of([
				'name', '$order',
				'type' => 'array'
			]));
		}
		//
		// Enforcing order configuration.
		if(!is_array($this->_cp_OrderBy)) {
			$this->_cp_OrderBy = [];
		}
		$order = $order ? $order : $this->_cp_OrderBy;
		//
		// Generating a proper query to obtain a list of IDs.
		$prefixes = $this->queryAdapterPrefixes();
		$query = $this->_db->queryAdapter()->select($this->_cp_Table, $conditions, $prefixes, $order);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Executing query and fetching IDs.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$idKey = "{$this->_cp_ColumnsPerfix}{$this->_cp_IDColumn}";
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
	 * This method retrieves an ID in the represented table based on a set of
	 * conditions.
	 *
	 * Conditions is an associative array where keys are field names without
	 * prefixes and associated values are values to look for.
	 *
	 * @param mixed[string] $conditions List of conditions to apply.
	 * @param string[string] $order Query sorting conditions.
	 * @return int Returns the first ID that matches.
	 */
	public function idBy($conditions, $order = []) {
		$ids = $this->idsBy($conditions, $order);
		return array_shift($ids);
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
		if(!$this->_cp_IDColumn) {
			throw new Exception(Translate::Instance()->EX_representation_has_no_ID_field_defined);
		}
		//
		// Obtaining an object to hold the represented entry.
		$item = self::GetClass($this->_cp_RepresentationClass, $this->_dbname);
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
	 * @param string $name Name to look for.
	 * @return \TooBasic\Representations\ItemRepresentation Returns a
	 * representation when found or NULL if not.
	 * @throws \TooBasic\Exception
	 */
	public function itemByName($name) {
		//
		// Checking if there's an name field configured, if not it means
		// this representation doesn't support this method.
		if(!$this->_cp_NameColumn) {
			throw new Exception(Translate::Instance()->EX_representation_has_no_name_field_defined);
		}
		//
		// Obtaining an object to hold the represented entry.
		$item = self::GetClass($this->_cp_RepresentationClass, $this->_dbname);
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
		$out = [];
		//
		// Fetching all available dis and creating a representation for
		// each one of them.
		foreach($this->ids() as $id) {
			$out[$id] = $this->item($id);
		}

		return $out;
	}
	/**
	 * This method retrieves a list of representations in the represented
	 * table based on a set of conditions.
	 *
	 * Conditions is an associative array where keys are field names without
	 * prefixes and associated values are values to look for.
	 *
	 * @param mixed[string] $conditions List of conditions to apply.
	 * @param string[string] $order Query sorting conditions.
	 * @return \TooBasic\Representations\ItemRepresentation[] Returns a list
	 * of representations.
	 */
	public function itemsBy($conditions, $order = []) {
		//
		// Default values.
		$out = [];
		//
		// Fetching all available dis and creating a representation for
		// each one of them.
		foreach($this->idsBy($conditions, $order) as $id) {
			$out[$id] = $this->item($id);
		}

		return $out;
	}
	/**
	 * This method retrieves a representations in the represented table based
	 * on a set of conditions.
	 *
	 * Conditions is an associative array where keys are field names without
	 * prefixes and associated values are values to look for.
	 *
	 * @param mixed[string] $conditions List of conditions to apply.
	 * @param string[string] $order Query sorting conditions.
	 * @return \TooBasic\Representations\ItemRepresentation Returns the first
	 * representations that matches.
	 */
	public function itemBy($conditions, $order = []) {
		$items = $this->itemsBy($conditions, $order);
		return array_shift($items);
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
			$this->_queryAdapterPrefixes = [
				GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
				GC_DBQUERY_PREFIX_COLUMN => $this->_cp_ColumnsPerfix
			];
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
		static $Instances = [];
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
				$Instances[$classname] = [];
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
