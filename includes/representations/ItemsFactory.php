<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

abstract class ItemsFactory {
	//
	// Constants.
	//
	// Public class properties.
	//
	// Protected class properties.
	protected static $_LoadedClasses = array();
	//
	// Protected core properties.
	protected $_CP_IDColumn = "";
	protected $_CP_ColumnsPerfix = "";
	protected $_CP_OrderBy = false;
	protected $_CP_RepresentationClass = "";
	protected $_CP_Table = "";
	//
	// Protected properties.	
	/**
	 * @var \TooBasic\DBAdapter
	 */
	protected $_db = false;
	protected $_dbname = false;
	protected $_dbprefix = "";
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
		trigger_error(get_called_class()."::".__FUNCTION__.": Clone is not allowed.", E_USER_ERROR);
	}
	//
	// Public methods.
	public function ids() {
		$out = array();

		$query = "select  {$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn} as id \n";
		$query.= "from    {$this->_dbprefix}{$this->_CP_Table} \n";
		if($this->_CP_OrderBy != false) {
			$query.= "order by $this->_CP_OrderBy \n";
		}

		$stmt = $this->_db->prepare($query);

		$stmt->execute();

		foreach($stmt->fetchAll() as $row) {
			$out[] = $row["id"];
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
		$name = classname($name)."Representation";

		if(!in_array($name, self::$_LoadedClasses)) {
			$filename = Paths::Instance()->representationPath($name);
			if($filename) {
				require_once $filename;

				if(class_exists($name)) {
					self::$_LoadedClasses[] = $name;
				} else {
					trigger_error("Class '{$name}' is not defined.", E_USER_ERROR);
				}
			} else {
				trigger_error("Cannot load item representation '{$name}'.", E_USER_ERROR);
			}
		}

		return new $name($dbname);
	}
}
