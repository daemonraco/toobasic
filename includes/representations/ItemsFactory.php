<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

abstract class ItemsFactory extends Singleton {
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
	protected $_CP_RepresentationClass = "";
	protected $_CP_Table = "";
	//
	// Protected properties.	
	/**
	 * @var \TooBasic\DBAdapter
	 */
	protected $_db = false;
	protected $_dbprefix = "";
	//
	// Magic methods.
	//
	// Public methods.
	public function ids() {
		$out = array();

		$query = "select  {$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn} as id \n";
		$query.= "from    {$this->_dbprefix}{$this->_CP_Table} \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute();

		foreach($stmt->fetchAll() as $row) {
			$out[] = $row["id"];
		}

		return $out;
	}
	public function item($id) {
		$item = self::GetClass($this->_CP_RepresentationClass);
		$item->load($id);

		if(!$item->exists()) {
			$item = null;
		}

		return $item;
	}
	public function itemByName($name) {
		$item = self::GetClass($this->_CP_RepresentationClass);
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
		parent::init();

		$this->_db = DBManager::Instance()->getDefault();
		$this->_dbprefix = $this->_db->prefix();
	}
	//
	// Public class methods.
	//
	// Protected class methods.
	protected static function GetClass($name) {
		$out = null;

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

		return new $name();
	}
}
