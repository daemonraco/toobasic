<?php

/**
 * @file QueryPostgreSQL.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

/**
 * @class QueryPostgreSQL
 */
class QueryPostgreSQL extends QueryAdapter {
	//
	// Public methods.
	/**
	 * This method creates a query to insert an empty entry inside a table.
	 * This means it will only have an ID.
	 *
	 * @param string $table Name of the table to be modified.
	 * @param mixed[string] $data List of values associated with their column
	 * names (without prefixes).
	 * @param string[string] $prefixes List of prefixes used on queries.
	 * @return mixed[string] Returns an associative array containing
	 * information about the query and the query itself.
	 */
	public function createEmptyEntry($table, $data = array(), &$prefixes = array()) {
		if(!isset($data[GC_DBQUERY_NAMES_COLUMN_ID])) {
			throw new \TooBasic\DBException('No name set for id column');
		}

		$this->cleanPrefixes($prefixes);


		$out = array(
			GC_AFIELD_ADAPTER => $this->_className,
			GC_AFIELD_QUERY => '',
			GC_AFIELD_PARAMS => array(),
			GC_AFIELD_SEQNAME => "{$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table}_{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$data[GC_DBQUERY_NAMES_COLUMN_ID]}_seq"
		);
		$out[GC_AFIELD_QUERY] = "insert \n";
		$out[GC_AFIELD_QUERY].= "        into {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table}({$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$data[GC_DBQUERY_NAMES_COLUMN_ID]}) \n";
		$out[GC_AFIELD_QUERY].= "        values (default) \n";

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
	public function deletePrepare($table, $whereFields, &$prefixes = array()) {
		$this->cleanPrefixes($prefixes);

		$where = array();
		foreach($whereFields as $key) {
			$xKey = self::ExpandFieldName($key);
			if($xKey[GC_AFIELD_RESULT] && $xKey[GC_AFIELD_FLAG] == '*') {
				$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$xKey[GC_AFIELD_NAME]} like :{$xKey[GC_AFIELD_NAME]}";
			} else {
				$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :{$key}";
			}
		}

		$query = "delete from {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table} \n";
		if($where) {
			$query.= "where       ".implode(' and ', $where)." \n";
		}

		return $query;
	}
	public function insertPrepare($table, $fields, &$prefixes = array()) {
		$this->cleanPrefixes($prefixes);

		$columns = array();
		$values = array();
		foreach($fields as $key) {
			$columns[] = $prefixes[GC_DBQUERY_PREFIX_COLUMN].$key;
			$values[] = ":{$key}";
		}

		$query = "insert \n";
		$query.= "        into {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table}(".implode(', ', $columns).") \n";
		$query.= '        values ('.implode(', ', $values).") \n";

		return $query;
	}
	public function selectPrepare($table, $whereFields, &$prefixes = array(), $orderBy = array(), $limit = false, $offset = false) {
		$this->cleanPrefixes($prefixes);
		//
		// Default values.
		$query = "select  * \n";
		//
		// Checking if this is a multi-table select.
		if(is_array($table)) {
			//
			// Building 'from' sentence.
			$first = true;
			$auxList = [];
			foreach($table as $t) {
				$aux = $first ? "from    " : "        ";
				$aux.= "{$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$t}";
				$auxList[] = $aux;

				$first = false;
			}
			$query.= implode(", \n", $auxList)." \n";
			//
			// Building 'where' sentence pieces.
			$where = array();
			foreach($whereFields as $key => $value) {
				$xKey = self::ExpandFieldName($key);
				if($xKey[GC_AFIELD_RESULT]) {
					switch($xKey[GC_AFIELD_FLAG]) {
						case 'C':
							$where[] = "{$xKey[GC_AFIELD_NAME]} = {$value}";
							break;
						case '*':
							$where[] = "{$xKey[GC_AFIELD_NAME]} like :{$xKey[GC_AFIELD_NAME]}";
							break;
					}
				} else {
					$where[] = "{$key} = :{$key}";
				}
			}
		} else {
			//
			// Building 'from' sentence.
			$query.= "from    {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table} \n";
			//
			// Building 'where' sentence pieces.
			$where = array();
			foreach($whereFields as $key => $value) {
				$xKey = self::ExpandFieldName($key);
				if($xKey[GC_AFIELD_RESULT] && $xKey[GC_AFIELD_FLAG] == '*') {
					$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$xKey[GC_AFIELD_NAME]} like :{$xKey[GC_AFIELD_NAME]}";
				} else {
					$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :{$key}";
				}
			}
		}
		//
		// Appending 'where' conditions.
		if($where) {
			$query.= "where   ".implode(' and ', $where)." \n";
		}
		//
		// Building 'order by' sentence.
		$order = array();
		foreach($orderBy as $field => $way) {
			$order[] = "{$field} {$way}";
		}
		if($order) {
			$query.= 'order by '.implode(', ', $order)." \n";
		}
		//
		// Limit.
		if($limit !== false) {
			$limit += 0;
			if($offset !== false) {
				$offset += 0;
				$query.= "limit {$limit} offset {$offset}\n";
			} else {
				$query.= "limit {$limit} \n";
			}
		}

		return $query;
	}
	public function updatePrepare($table, $dataFields, $whereFields, &$prefixes = array()) {
		$this->cleanPrefixes($prefixes);

		$sets = array();
		foreach($dataFields as $key) {
			$sets[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :d_{$key}";
		}
		$where = array();
		foreach($whereFields as $key) {
			$xKey = self::ExpandFieldName($key);
			if($xKey[GC_AFIELD_RESULT] && $xKey[GC_AFIELD_FLAG] == '*') {
				$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$xKey[GC_AFIELD_NAME]} like :w_{$xKey[GC_AFIELD_NAME]}";
			} else {
				$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :w_{$key}";
			}
		}

		$query = "update  {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table} \n";
		$query.= "set     ".implode(', ', $sets)." \n";
		if($where) {
			$query.= "where   ".implode(' and ', $where)." \n";
		}

		return $query;
	}
}
