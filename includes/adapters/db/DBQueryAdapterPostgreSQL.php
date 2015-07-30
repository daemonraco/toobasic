<?php

/**
 * @file DBQueryAdapterPostgreSQL.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

/**
 * @class QueryPostgreSQL
 */
class QueryPostgreSQL extends QueryAdapter {
	//
	// Public methods.
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
	public function deletePrepare($table, $whereFields, &$prefixes = array()) {
		$this->cleanPrefixes($prefixes);

		$where = array();
		foreach($whereFields as $key) {
			$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :{$key}";
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

		$where = array();
		foreach($whereFields as $key) {
			$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :{$key}";
		}

		$order = array();
		foreach($orderBy as $field => $way) {
			$order[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$field} {$way}";
		}
		$query = "select  * \n";
		$query.= "from    {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table} \n";
		if($where) {
			$query.= "where   ".implode(' and ', $where)." \n";
		}
		if($order) {
			$query.= 'order by '.implode(', ', $order)." \n";
		}
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
			$where[] = "{$prefixes[GC_DBQUERY_PREFIX_COLUMN]}{$key} = :w_{$key}";
		}

		$query = "update  {$prefixes[GC_DBQUERY_PREFIX_TABLE]}{$table} \n";
		$query.= "set     ".implode(', ', $sets)." \n";
		if($where) {
			$query.= "where   ".implode(' and ', $where)." \n";
		}

		return $query;
	}
}
