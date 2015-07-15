<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

abstract class ItemsFactory {
	//
	// Protected class properties.
	protected static $_LoadedClasses = array();
	//
	// Protected core properties.
	protected $_CP_IDColumn = '';
	protected $_CP_ColumnsPerfix = '';
	protected $_CP_NameColumn = 'name';
	protected $_CP_OrderBy = false;
	protected $_CP_RepresentationClass = '';
	protected $_CP_Table = '';
	//
	// Protected properties.	
	/**
	 * @var \TooBasic\DBAdapter
	 */
	protected $_db = false;
	protected $_dbname = false;
	protected $_dbprefix = '';
	protected $_queryAdapterPrefixes = false;
	//
	// Magic methods.
	/**
	 * Prevent users from directly creating the singleton's instance.
	 */
	protected function __constructor() {
		
	}
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		throw new Exception('Clone is not allowed');
	}
	//
	// Public methods.
	public function create() {
		$out = false;

		$query = $this->_db->queryAdapter()->createEmptyEntry($this->_CP_Table, array(
			GC_DBQUERY_NAMES_COLUMN_ID => $this->_CP_IDColumn,
			GC_DBQUERY_NAMES_COLUMN_NAME => $this->_CP_NameColumn
			), $this->queryAdapterPrefixes());
		$stmt = $this->_db->prepare($query['query']);
		if($stmt->execute($query['params'])) {
			$out = $this->_db->lastInsertId();
		}

		return $out;
	}
	public function ids() {
		$out = array();

		if(!is_array($this->_CP_OrderBy)) {
			$this->_CP_OrderBy = array();
		}
		$query = $this->_db->queryAdapter()->select($this->_CP_Table, array(), $this->queryAdapterPrefixes(), $this->_CP_OrderBy);
		$stmt = $this->_db->prepare($query['query']);
		if($stmt->execute($query['params'])) {
			$idKey = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
			foreach($stmt->fetchAll() as $row) {
				$out[] = $row[$idKey];
			}
		}

		return $out;
	}
	public function item($id) {
		$item = self::GetClass($this->_CP_RepresentationClass, $this->_dbname);
		$item->load($id);

		if(!$item->exists()) {
			$item = null;
		}

		return $item;
	}
	public function itemByName($name) {
		$item = self::GetClass($this->_CP_RepresentationClass, $this->_dbname);
		$item->loadByName($name);

		if(!$item->exists()) {
			$item = null;
		}

		return $item;
	}
	public function items() {
		$out = array();

		foreach($this->ids() as $id) {
			$out[$id] = $this->item($id);
		}

		return $out;
	}
	//
	// Protected methods.
	protected function init() {
		$this->_db = DBManager::Instance()->{$this->_dbname};
		$this->_dbprefix = $this->_db->prefix();
	}
	protected function queryAdapterPrefixes() {
		if(!$this->_queryAdapterPrefixes === false) {
			$this->_queryAdapterPrefixes = array(
				GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
				GC_DBQUERY_PREFIX_COLUMN => $this->_CP_ColumnsPerfix
			);
		}
		return $this->_queryAdapterPrefixes;
	}
	//
	// Public class methods.
	final public static function &Instance($dbname = false) {
		static $Instances = array();

		if($dbname === false) {
			global $Connections;
			$dbname = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
		}

		$classname = get_called_class();
		if(!isset($Instances[$classname][$dbname])) {
			if(!isset($Instances[$classname])) {
				$Instances[$classname] = array();
			}
			$Instances[$classname][$dbname] = new $classname();
			$Instances[$classname][$dbname]->_dbname = $dbname;
			$Instances[$classname][$dbname]->init();
		}

		return $Instances[$classname][$dbname];
	}
	//
	// Protected class methods.
	protected static function GetClass($name, $dbname) {
		$className = Names::ItemRepresentationClass($name);

		if(!in_array($className, self::$_LoadedClasses)) {
			$filename = Paths::Instance()->representationPath(Names::ItemRepresentationFilename($name));
			if($filename) {
				require_once $filename;

				if(class_exists($className)) {
					self::$_LoadedClasses[] = $className;
				} else {
					throw new Exception("Class '{$className}' is not defined");
				}
			} else {
				throw new Exception("Cannot load item representation '{$className}'");
			}
		}

		return new $className($dbname);
	}
}
