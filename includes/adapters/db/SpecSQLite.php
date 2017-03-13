<?php

/**
 * @file SpecSQLite.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

use TooBasic\Managers\DBStructureManager;
use TooBasic\Translate;

/**
 * @class SpecSQLite
 */
class SpecSQLite extends SpecAdapter {
	//
	// Protected properties.
	protected $_engine = 'SQLite';
	//
	// Public methods.
	public function addTableEntry(\stdClass $table, \stdClass $entry) {
		$keys = [];
		$values = [];
		foreach($entry->entry as $key => $value) {
			$keys[] = $key;
			$values[] = str_replace("'", "''", $value);
		}

		$query = "insert \n";
		$query.= "        into {$table->fullname} (".implode(",", $keys).") \n";
		$query.= "        values ('".implode("','", $values)."') \n";

		return $this->exec($query);
	}
	public function checkTableEntry(\stdClass $table, \stdClass $entry) {
		$query = "select  * \n";
		$query.= "from    {$table->fullname} \n";
		$query.= "where   1 = 1 \n";
		foreach($entry->check as $field) {
			$query.= " and    {$field} = '{$entry->entry->$field}' \n";
		}

		return count($this->_db->queryData($query, false)) > 0;
	}
	public function compareIndex(\stdClass $index) {
		$ok = true;

		$query = "pragma index_info({$index->fullname}) ";

		$indexSpecs = $this->_db->queryData($query);

		$dbColumnCount = count($indexSpecs);
		$spectColumnCount = count($index->fields);

		if($dbColumnCount == $spectColumnCount) {
			for($position = 0; $position < $dbColumnCount; $position++) {
				if($indexSpecs[$position]['name'] != $index->fields[$position]) {
					$ok = false;
					break;
				}
			}
		} else {
			$ok = false;
		}

		return $ok;
	}
	public function compareTable(\stdClass $table, &$creates, &$drops, &$updates) {
		$query = "pragma table_info({$table->fullname}) ";
		$tableSpecs = $this->_db->queryData($query);
		$autoIncrement = count($this->_db->queryData("select * from sqlite_master where type = 'table' and name = '{$table->fullname}' and sql like '%autoincrement%'")) > 0;
		//
		// New columns.
		$cmp = [];
		foreach($table->fields as $fullname => $field) {
			$found = false;
			foreach($tableSpecs as $dbColumn) {
				if($fullname == $dbColumn['name']) {
					$found = $dbColumn;
					continue;
				}
			}
			if(!$found) {
				$creates[] = $fullname;
			} else {
				$cmp[$fullname] = [
					GC_AFIELD_DB => $found,
					GC_AFIELD_SPEC => null
				];
			}
		}
		//
		// Old columns.
		foreach($tableSpecs as $dbColumn) {
			if(!isset($table->fields[$dbColumn['name']])) {
				$drops[] = $dbColumn['name'];
			} else {
				$spec = $table->fields[$dbColumn['name']];
				$spec->builtType = $this->buildColumnType($spec->type, $spec->autoincrement);
				$cmp[$dbColumn['name']][GC_AFIELD_SPEC] = $spec;
			}
		}
		//
		// Different columns.
		foreach($cmp as $fullname => $data) {
			if($data[GC_AFIELD_DB]['type'] != $data[GC_AFIELD_SPEC]->builtType) {
				$updates[] = $fullname;
				continue;
			}
			if($data[GC_AFIELD_SPEC]->autoincrement != $autoIncrement) {
				$updates[] = $fullname;
				continue;
			}
			if($data[GC_AFIELD_SPEC]->null == $data[GC_AFIELD_DB]['notnull']) {
				$updates[] = $fullname;
				continue;
			}
			if($data[GC_AFIELD_SPEC]->hasDefault) {
				if(($data[GC_AFIELD_SPEC]->default === null && $data[GC_AFIELD_DB]['dflt_value'] != 'null') || $data[GC_AFIELD_SPEC]->default != $data[GC_AFIELD_DB]['dflt_value']) {
					$updates[] = $fullname;
					continue;
				}
			} elseif($data[GC_AFIELD_DB]['dflt_value']) {
				$updates[] = $fullname;
				continue;
			}
		}
		//
		// SQLite does not supports column modifications or dropping @{
		$drops = [];
		$updates = [];
		// @}
	}
	public function createIndex(\stdClass $index) {
		$query = 'create ';
		switch($index->type) {
			case 'index':
				$query.= 'index ';
				break;
			case 'key':
			case 'primary':
				$query.= 'unique index ';
				break;
		}
		$query.= "{$index->fullname} \n";
		$query.= "        on {$index->table} (\n";

		$lines = [];
		foreach($index->fields as $field) {
			$lines[] = "                {$field}";
		}

		$query.= implode(", \n", $lines)." \n";

		$query.= "        )\n";

		return $this->exec($query);
	}
	public function createTable(\stdClass $table) {
		$query = "create table {$table->fullname} ( \n";

		$lines = [];
		foreach($table->fields as $field) {
			$lines[] = "        {$this->buildFullColumnType($field)}";
		}

		$query.= implode(", \n", $lines)." \n";

		$query.= ') ';

		return $this->exec($query);
	}
	public function createTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        add column {$field->fullname} {$this->buildFullColumnType($field, false)}";

		return $this->exec($query);
	}
	public function dropIndex($indexName) {
		return $this->exec("drop index {$indexName}");
	}
	public function dropTable($tableName) {
		$query = "drop table {$tableName}";
		return $this->exec($query);
	}
	public function dropTableColumn(\stdClass $table, $columnName) {
		throw new Exception(Translate::Instance()->EX_SQLite_not_supported(['operation' => 'DROP COLUMN']));
	}
	public function getIndexes() {
		$out = [];

		$query = "select  distinct name \n";
		$query.= "from    sqlite_master \n";
		$query.= "where   type = 'index' \n";

		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row['name'];
		}

		return $out;
	}
	public function getTables() {
		$out = [];

		$query = "select  distinct name \n";
		$query.= "from    sqlite_master \n";
		$query.= "where   type = 'table' \n";
		$query.= "where   name not like 'sqlite_%' \n";

		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row['name'];
		}

		return $out;
	}
	public function indexExists($indexName) {
		$query = "pragma index_info({$indexName})";

		return count($this->_db->queryData($query, false)) > 0;
	}
	public function tableExists($tableName) {
		$query = "select  * \n";
		$query.= "from    sqlite_master \n";
		$query.= "where   type = 'table' \n";
		$query.= " and    name = '{$tableName}'";

		return count($this->_db->queryData($query, false)) == 1;
	}
	public function updateIndex(\stdClass $index) {
		return $this->dropIndex($index->fullname) && $this->createIndex($index);
	}
	public function updateTableColumn(\stdClass $table, $columnName) {
		throw new Exception(Translate::Instance()->EX_SQLite_not_supported(['operation' => 'MODIFY COLUMN']));
	}
	//
	// Protected methods.
	protected function buildColumnType($type, $isAutoincremente) {
		$out = '';

		if($isAutoincremente) {
			$out = 'integer';
		} else {
			switch($type->type) {
				case DBStructureManager::COLUMN_TYPE_BLOB:
					$out = 'blob';
					break;
				case DBStructureManager::COLUMN_TYPE_ENUM:
					$length = 0;
					foreach($type->values as $val) {
						$len = strlen($val);
						$length = $len > $length ? $len : $length;
					}
					$out = "varchar({$length})";
					break;
				case DBStructureManager::COLUMN_TYPE_FLOAT:
					$out = "float({$type->precision})";
					break;
				case DBStructureManager::COLUMN_TYPE_TEXT:
					$out = 'text';
					break;
				case DBStructureManager::COLUMN_TYPE_VARCHAR:
					$out = "varchar({$type->precision})";
					break;
				case DBStructureManager::COLUMN_TYPE_TIMESTAMP:
					$out = 'timestamp';
					break;
				case DBStructureManager::COLUMN_TYPE_INT:
					$out = "int({$type->precision})";
					break;
				default:
					break;
			}
		}

		return $out;
	}
	protected function buildFullColumnType($spec, $includeName = true) {
		$out = '';

		if($includeName) {
			$out = "{$spec->fullname} {$this->buildColumnType($spec->type, $spec->autoincrement)} ";
		} else {
			$out = "{$this->buildColumnType($spec->type, $spec->autoincrement)} ";
		}
		if(!$spec->null) {
			$out.= 'not null ';
		}
		if($spec->hasDefault) {
			if($spec->default === null) {
				if($spec->null) {
					$out.= 'default null ';
				}
			} else {
				if(in_array($spec->type->type, [DBStructureManager::COLUMN_TYPE_BLOB, DBStructureManager::COLUMN_TYPE_TEXT, DBStructureManager::COLUMN_TYPE_VARCHAR])) {
					$out.= "default '{$spec->default}' ";
				} else {
					$out.= "default {$spec->default} ";
				}
			}
		}
		if($spec->autoincrement) {
			/** @fixme this is somehow impolite @{ */
			if($includeName) {
				$out.= 'primary key autoincrement ';
			} else {
				$out.= 'autoincrement ';
			}
			/** @} */
		}

		return $out;
	}
	protected function specificExecuteCallback($data) {
		$out = true;

		foreach(explode(';', $data) as $query) {
			$query = trim($query);
			if(!$query) {
				continue;
			}

			$out = $this->exec($query);
			if(!$out) {
				break;
			}
		}

		return $out;
	}
}
