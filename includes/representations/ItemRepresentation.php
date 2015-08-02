<?php

/**
 * @file ItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

use TooBasic\Managers\DBManager;

/**
 * @abstract
 * @class ItemRepresentation
 */
abstract class ItemRepresentation {
	//
	// Protected core properties.
	protected $_CP_ColumnsPerfix = '';
	protected $_CP_IDColumn = '';
	protected $_CP_NameColumn = 'name';
	protected $_CP_ReadOnlyColumns = array();
	protected $_CP_Table = '';
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter
	 */
	protected $_db = false;
	protected $_dbprefix = '';
	protected $_dirty = false;
	protected $_exists = false;
	protected $_extraProperties = array();
	protected $_lastDBError = false;
	protected $_properties = array();
	protected $_queryAdapterPrefixes = false;
	//
	// Magic methods.
	public function __construct($dbname) {
		$this->_db = DBManager::Instance()->{$dbname};
		$this->_dbprefix = $this->_db->prefix();
	}
	public function __toString() {
		return $this->exists() ? get_called_class().'()[('.$this->{$this->_CP_IDColumn}.')]' : 'NULL';
	}
	public function __get($name) {
		$out = null;

		$realName = "{$this->_CP_ColumnsPerfix}{$name}";
		if(isset($this->_properties[$realName])) {
			$out = $this->_properties[$realName];
		} elseif(isset($this->_extraProperties[$name])) {
			$out = $this->_extraProperties[$name];
		}

		return $out;
	}
	public function __set($name, $value) {
		$realName = "{$this->_CP_ColumnsPerfix}{$name}";
		if(isset($this->_properties[$realName])) {
			if($name != $this->_CP_IDColumn && !in_array($name, $this->_CP_ReadOnlyColumns) && $this->_properties[$realName] != $value) {
				$this->_properties[$realName] = $value;
				$this->_dirty = true;
			} else {
				$value = $this->_properties[$realName];
			}
		} else {
			$this->_extraProperties[$name] = $value;
		}

		return $value;
	}
	//
	// Public methods.
	public function dirty() {
		return $this->_dirty;
	}
	public function exists() {
		return $this->_exists;
	}
	public function lastDBError() {
		return $this->_lastDBError;
	}
	public function load($id) {
		$this->reset();
		$this->preLoad($id);

		$query = $this->_db->queryAdapter()->select($this->_CP_Table, array($this->_CP_IDColumn => $id), $this->queryAdapterPrefixes());
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);

		$data = false;
		if(!$stmt->execute($query[GC_AFIELD_PARAMS])) {
			$this->_lastDBError = $stmt->errorInfo();
		} else {
			$data = $stmt->fetch();
		}

		if($data) {
			$this->_properties = $data;
			$this->_exists = true;
			$this->postLoad();
		} else {
			$this->_exists = false;
		}

		return $this->exists();
	}
	public function loadByName($name) {
		$this->reset();

		if($this->_CP_NameColumn) {
			$query = $this->_db->queryAdapter()->select($this->_CP_Table, array($this->_CP_NameColumn => $name), $this->queryAdapterPrefixes());
			$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
			if($stmt->execute($query[GC_AFIELD_PARAMS])) {
				$row = $stmt->fetch();
				//
				// Checking if something was actually found.
				if($row) {
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
	public function persist() {
		$persisted = false;

		if($this->dirty() && $this->prePersist()) {
			$idName = "{$this->_CP_ColumnsPerfix}{$this->_CP_IDColumn}";
			$data = array();

			foreach($this->_properties as $key => $value) {
				$shortKey = substr($key, strlen($this->_CP_ColumnsPerfix));
				if($idName != $key && !in_array($shortKey, $this->_CP_ReadOnlyColumns)) {
					$data[$shortKey] = $value;
				}
			}

			$query = $this->_db->queryAdapter()->update($this->_CP_Table, $data, array($this->_CP_IDColumn => $this->id), $this->queryAdapterPrefixes());
			$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);

			if($stmt->execute($query[GC_AFIELD_PARAMS])) {
				$persisted = true;
				$this->_dirty = false;
			} else {
				$this->_lastDBError = $stmt->errorInfo();
			}
		}

		return $persisted;
	}
	public function remove() {
		$query = $this->_db->queryAdapter()->delete($this->_CP_Table, array($this->_CP_IDColumn => $this->id), $this->queryAdapterPrefixes());
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		if(!$stmt->execute($query[GC_AFIELD_PARAMS])) {
			$this->_lastDBError = $stmt->errorInfo();
		}

		$this->load($this->id);

		return !$this->exists();
	}
	public function reset() {
		$this->_dirty = false;
		$this->_exists = false;
		$this->_extraProperties = array();
		$this->_properties = array();
	}
	public function toArray() {
		$out = $this->_extraProperties;

		foreach($this->_properties as $key => $value) {
			$out[substr($key, strlen($this->_CP_ColumnsPerfix))] = $value;
		}

		return $out;
	}
	//
	// Protected methods.
	protected function preLoad($id) {
		
	}
	protected function postLoad() {
		
	}
	protected function prePersist() {
		return true;
	}
	protected function postPersist() {
		
	}
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
