<?php

/**
 * @file QueryAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

//
// Class aliases.
use TooBasic\DBException;
use TooBasic\Translate;

/**
 * @class QueryAdapter
 * @abstract
 * This is a generic adapter to generate some basic SQL queries with the proper
 * syntax.
 */
abstract class QueryAdapter extends \TooBasic\Adapters\Adapter {
	//
	// Protected properties.
	/**
	 * @var string current class name.
	 */
	protected $_className = __CLASS__;
	/**
	 * @var boolean This flag indicates that queries have to be prompted for
	 * analysis.
	 */
	protected $_debugQueries = false;
	//
	// Public methods.
	/**
	 * This method creates a query to insert an empty entry inside a table.
	 * This means it will only have an ID.
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $data List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 */
	abstract public function createEmptyEntry($table, $data = [], &$prefixes = []);
	/**
	 * This method generates a proper query to remove certain entry and all
	 * the assets required to execute it.
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $where List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 */
	public function delete($table, $where, &$prefixes = []) {
		//
		// Creating a response structure and generating some of its
		// values.
		$out = [
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->deletePrepare($table, array_keys($where), $prefixes),
			GC_AFIELD_PARAMS => []
		];
		//
		// Building the list of params to use in a statement execution.
		foreach($where as $key => $value) {
			$xKey = self::ExpandFieldName($key);
			if($xKey[GC_AFIELD_RESULT] && $xKey[GC_AFIELD_FLAG] == '*') {
				$out[GC_AFIELD_PARAMS][":{$xKey[GC_AFIELD_NAME]}"] = "%{$value}%";
			} else {
				$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
			}
		}
		//
		// Debugging.
		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DEBUG_THING_TYPE_OK);
		}

		return $out;
	}
	/**
	 * This method builds a query that can be use to create a deletion
	 * statement.
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $whereFields List of values associated with their
	 * column names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return string Returns a query useful to create a statmente.
	 */
	abstract public function deletePrepare($table, $whereFields, &$prefixes = []);
	/**
	 * This method generates a proper query to insert an entry and all the
	 * assets required to execute it.
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $data List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 */
	public function insert($table, $data, &$prefixes = []) {
		//
		// Creating a response structure and generating some of its
		// values.
		$out = [
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->insertPrepare($table, array_keys($data), $prefixes),
			GC_AFIELD_PARAMS => [],
			GC_AFIELD_SEQNAME => null
		];
		//
		// Building the list of params to use in a statement execution.
		foreach($data as $key => $value) {
			$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
		}
		//
		// Debugging.
		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DEBUG_THING_TYPE_OK);
		}

		return $out;
	}
	/**
	 * This method builds a query that can be use to create a deletion
	 * statement.
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $fields List of values associated with their
	 * column names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return string Returns a query useful to create a statmente.
	 */
	abstract public function insertPrepare($table, $fields, &$prefixes = []);
	/**
	 * @todo doc
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $where List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @param string[string] $orderBy List of column names (without prefixes)
	 * associated with a order direction (asc/desc).
	 * @param int $limit Maximum amount of entries to be returned.
	 * @param int $offset Position from which start retrieving rows.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 */
	public function select($table, $where, &$prefixes = [], $orderBy = [], $limit = false, $offset = false) {
		//
		// Creating a response structure and generating some of its
		// values.
		$out = [
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->selectPrepare($table, $where, $prefixes, $orderBy, $limit, $offset),
			GC_AFIELD_PARAMS => []
		];
		//
		// Building the list of params to use in a statement execution.
		foreach($where as $key => $value) {
			$xKey = self::ExpandFieldName($key);

			switch($xKey[GC_AFIELD_FLAG]) {
				case '*':
					$out[GC_AFIELD_PARAMS][":{$xKey[GC_AFIELD_NAME]}"] = "%{$value}%";
					break;
				case 'C':
					break;
				default:
					if($xKey[GC_AFIELD_RESULT]) {
						$out[GC_AFIELD_PARAMS][":{$xKey[GC_AFIELD_NAME]}"] = $value;
					} else {
						$out[GC_AFIELD_PARAMS][":{$key}"] = $value;
					}
					break;
			}
		}
		//
		// Debugging.
		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DEBUG_THING_TYPE_OK);
		}

		return $out;
	}
	abstract public function selectPrepare($table, $whereFields, &$prefixes = [], $orderBy = [], $limit = false, $offset = false);
	/**
	 * @todo doc
	 *
	 * @param string $table Name of the table to be modified (without
	 * prefixes).
	 * @param mixed[string] $data List of values associated with their column
	 * names (without prefixes).
	 * @param mixed[string] $where List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 * @throws \TooBasic\DBException
	 */
	public function update($table, $data, $where, &$prefixes = []) {
		if(!count($data)) {
			throw new DBException(Translate::Instance()->EX_no_data_to_be_set_given);
		}
		//
		// Creating a response structure and generating some of its
		// values.
		$out = [
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => $this->updatePrepare($table, array_keys($data), array_keys($where), $prefixes),
			GC_AFIELD_PARAMS => []
		];
		//
		// Building the list of params to use in a statement execution @{
		foreach($data as $key => $value) {
			$out[GC_AFIELD_PARAMS][":d_{$key}"] = $value;
		}
		foreach($where as $key => $value) {
			$xKey = self::ExpandFieldName($key);
			if($xKey[GC_AFIELD_RESULT] && $xKey[GC_AFIELD_FLAG] == '*') {
				$out[GC_AFIELD_PARAMS][":w_{$xKey[GC_AFIELD_NAME]}"] = "%{$value}%";
			} else {
				$out[GC_AFIELD_PARAMS][":w_{$key}"] = $value;
			}
		}
		// @}
		//
		// Debugging.
		if($this->_debugQueries) {
			\TooBasic\debugThing($out, \TooBasic\DEBUG_THING_TYPE_OK);
		}

		return $out;
	}
	abstract public function updatePrepare($table, $dataFields, $whereFields, &$prefixes = []);
	//
	// Protected methods.
	protected function cleanPrefixes(&$prefixes) {
		static $requiredPrefixes = [
			GC_DBQUERY_PREFIX_COLUMN => '',
			GC_DBQUERY_PREFIX_TABLE => ''
		];
		foreach($requiredPrefixes as $reqPfx => $defaultValue) {
			if(!isset($prefixes[$reqPfx])) {
				$prefixes[$reqPfx] = $defaultValue;
			}
		}
	}
	//
	// Protected methods.
	/**
	 * Instance initializer.
	 */
	protected function init() {
		$params = \TooBasic\Params::Instance();
		$this->_debugQueries = isset($params->debugdbquery);
		$this->_className = get_called_class();
	}
	//
	// Protected class methods.
	/**
	 * This class method tries to expand a field name into a flag character
	 * and its clean name. For example the name '*username' will be expanded
	 * into:
	 * 	- flag: '*'
	 * 	- name: 'username'
	 *
	 * @param string $name Name to expand.
	 * @return mixed[string] Expanded result.
	 */
	protected static function ExpandFieldName($name) {
		//
		// Response strucutre.
		$out = [
			GC_AFIELD_FLAG => '',
			GC_AFIELD_NAME => $name,
			GC_AFIELD_RESULT => false
		];
		//
		// Expansion pattern.
		$pattern = '/^((?P<flag>[*cC><!]{0,1}):)(?P<name>.*)$/';
		//
		// Checking field name.
		$out[GC_AFIELD_RESULT] = preg_match($pattern, $name, $matches);
		$out[GC_AFIELD_FLAG] = isset($matches['flag']) ? strtoupper($matches['flag']) : false;
		$out[GC_AFIELD_NAME] = isset($matches['name']) ? $matches['name'] : false;

		return $out;
	}
}
