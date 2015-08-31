<?php

/**
 * @file QueryAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

/**
 * @class QueryAdapter
 * @abstract
 */
abstract class QueryAdapter extends \TooBasic\Adapters\Adapter {
	//
	// Protected properties.
	protected $_className = __CLASS__;
	protected $_debugQueries = false;
	//
	// Public methods.
	abstract public function createEmptyEntry($table, $data = array(), &$prefixes = array());
	public function delete($table, $where, &$prefixes = array()) {
		$out = array(
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->deletePrepare($table, array_keys($where), $prefixes),
			GC_AFIELD_PARAMS => array()
		);

		foreach($where as $key => $value) {
			$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function deletePrepare($table, $whereFields, &$prefixes = array());
	public function insert($table, $data, &$prefixes = array()) {
		$out = array(
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->insertPrepare($table, array_keys($data), $prefixes),
			GC_AFIELD_PARAMS => array(),
			GC_AFIELD_SEQNAME => null
		);

		foreach($data as $key => $value) {
			$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
		}

		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DebugThingTypeOk);
		}

		return $out;
	}
	abstract public function insertPrepare($table, $fields, &$prefixes = array());
	public function select($table, $where, &$prefixes = array(), $orderBy = array(), $limit = false, $offset = false) {
		$out = array(
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->selectPrepare($table, array_keys($where), $prefixes, $orderBy, $limit, $offset),
			GC_AFIELD_PARAMS => array()
		);

		foreach($where as $key => $value) {
			$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
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
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->updatePrepare($table, array_keys($data), array_keys($where), $prefixes),
			GC_AFIELD_PARAMS => array()
		);

		foreach($data as $key => $value) {
			$out[GC_AFIELD_PARAMS][":d_{$key}"] = $value;
		}
		foreach($where as $key => $value) {
			$out[GC_AFIELD_PARAMS][":w_{$key}"] = $value;
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
