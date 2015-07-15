<?php

/**
 * @file DBQueryAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class DBQueryAdapter
 * @abstract
 */
abstract class DBQueryAdapter extends Adapter {
	//
	// Protected properties.
	protected $_className = 'DBQueryAdapter';
	protected $_debugQueries = false;
	//
	// Public methods.
	abstract public function createEmptyEntry($table, $data = array(), &$prefixes = array());
	public function delete($table, $where, &$prefixes = array()) {
		$out = array(
			'adapter' => $this->_className,
			'query' => $this->deletePrepare($table, array_keys($where), $prefixes),
			'params' => array()
		);

		foreach($where as $key => $value) {
			$out['params'][":{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function deletePrepare($table, $whereFields, &$prefixes = array());
	public function insert($table, $data, &$prefixes = array()) {
		$out = array(
			'adapter' => $this->_className,
			'query' => $this->insertPrepare($table, array_keys($data), $prefixes),
			'params' => array()
		);

		foreach($data as $key => $value) {
			$out['params'][":{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function insertPrepare($table, $fields, &$prefixes = array());
	public function select($table, $where, &$prefixes = array(), $orderBy = array(), $limit = false, $offset = false) {
		$out = array(
			'adapter' => $this->_className,
			'query' => $this->selectPrepare($table, array_keys($where), $prefixes, $orderBy, $limit, $offset),
			'params' => array()
		);

		foreach($where as $key => $value) {
			$out['params'][":{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function selectPrepare($table, $whereFields, &$prefixes = array(), $orderBy = array(), $limit = false, $offset = false);
	public function update($table, $data, $where, &$prefixes = array()) {
		if(!count($data)) {
			throw new \TooBasic\DBException("No data to be set given");
		}

		$out = array(
			'adapter' => $this->_className,
			'query' => $this->updatePrepare($table, array_keys($data), array_keys($where), $prefixes),
			'params' => array()
		);

		foreach($data as $key => $value) {
			$out['params'][":d_{$key}"] = $value;
		}
		foreach($where as $key => $value) {
			$out['params'][":w_{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function updatePrepare($table, $dataFields, $whereFields, &$prefixes = array());
	//
	// Protected methods.
	protected function cleanPrefixes(&$prefixes) {
		static $requiredPrefixes = array(
			GC_DBQUERY_PREFIX_COLUMN,
			GC_DBQUERY_PREFIX_TABLE
		);
		foreach($requiredPrefixes as $reqPfx) {
			if(!isset($prefixes[$reqPfx])) {
				$prefixes[$reqPfx] = '';
			}
		}
	}
	//
	// Protected methods.
	protected function init() {
		$params = \TooBasic\Params::Instance();
		$this->_debugQueries = isset($params->debugdbquery);
		$this->_className = get_called_class();
	}
}
