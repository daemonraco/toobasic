<?php

namespace TooBasic;

class DBSpecAdapterSQLite extends DBSpecAdapter {
	//
	// Protected properties.
	protected $_engine = 'SQLite';
	//
	// Public methods.
	public function addTableEntry(\stdClass $table, \stdClass $entry) {
		$keys = array();
		$values = array();
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
				if($indexSpecs[$position]["name"] != $index->fields[$position]) {
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
		$cmp = array();
		foreach($table->fields as $fullname => $field) {
			$found = false;
			foreach($tableSpecs as $dbColumn) {
				if($fullname == $dbColumn["name"]) {
					$found = $dbColumn;
					continue;
				}
			}
			if(!$found) {
				$creates[] = $fullname;
			} else {
				$cmp[$fullname] = array(
					"db" => $found,
					"spec" => null
				);
			}
		}
		//
		// Old columns.
		foreach($tableSpecs as $dbColumn) {
			if(!isset($table->fields[$dbColumn["name"]])) {
				$drops[] = $dbColumn["name"];
			} else {
				$spec = $table->fields[$dbColumn["name"]];
				$spec->builtType = $this->buildColumnType($spec->type);
				$cmp[$dbColumn["name"]]["spec"] = $spec;
			}
		}
		//
		// Different columns.
		foreach($cmp as $fullname => $data) {
			if($data["db"]["type"] != $data["spec"]->builtType) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->autoincrement != $autoIncrement) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->null == $data["db"]["notnull"]) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->hasDefault) {
				if(($data["spec"]->default === null && $data["db"]["dflt_value"] != "null") || $data["spec"]->default != $data["db"]["dflt_value"]) {
					$updates[] = $fullname;
					continue;
				}
			} elseif($data["db"]["dflt_value"]) {
				$updates[] = $fullname;
				continue;
			}
#			if($data["db"]["column_comment"] != $data["spec"]->comment) {
#				$updates[] = $fullname;
#				continue;
#			}
		}
		//
		// SQLite does not supports column modifications or dropping @{
		$drops = array();
		$updates = array();
		// @}
	}
	public function createIndex(\stdClass $index) {
		$query = "create ";
		switch($index->type) {
			case "index":
				$query.= "index ";
				break;
			case "key":
			case "primary":
				$query.= "unique index ";
				break;
		}
		$query.= "{$index->fullname} \n";
		$query.= "        on {$index->table} (\n";

		$lines = array();
		foreach($index->fields as $field) {
			$lines[] = "                {$field}";
		}

		$query.= implode(", \n", $lines)." \n";

		$query.= "        )\n";

		return $this->exec($query);
	}
	public function createTable(\stdClass $table) {
		$query = "create table {$table->fullname} ( \n";

		$lines = array();
		foreach($table->fields as $field) {
			$lines[] = "        {$this->buildFullColumnType($field)}";
		}

		$query.= implode(", \n", $lines)." \n";

		$query.= ") ";
#		if(isset($table->engine)) {
#			$query.= "engine={$table->engine} ";
#		}
#		$query.= " default charset=utf8 collate=utf8_bin auto_increment=1 ";

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
		throw new Exception('Operation not supported by SQLite (DROP COLUMN)');
		$query = "alter table {$table->fullname} \n";
		$query.= "        drop column {$columnName}";

		return $this->exec($query);
	}
	public function getIndexes() {
		$out = array();

		$query = "select  distinct name \n";
		$query.= "from    sqlite_master \n";
		$query.= "where   type = 'index' \n";

		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row["name"];
		}

		return $out;
	}
	public function getTables() {
		$out = array();

		$query = "select  distinct name \n";
		$query.= "from    sqlite_master \n";
		$query.= "where   type = 'table' \n";
		$query.= "where   name not like 'sqlite_%' \n";

		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row["name"];
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
		throw new Exception('Operation not supported by SQLite (MODIFY COLUMN)');
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        modify column {$field->fullname} {$this->buildFullColumnType($field, false)}";

		return $this->exec($query);
	}
	//
	// Protected methods.
	protected function buildColumnType($type, $isAutoincremente = false) {
		$out = "";

		if($isAutoincremente) {
			$out = "integer";
		} else {
			switch($type->type) {
				case DBStructureManager::ColumnTypeBlob:
					$out = "blob";
					break;
				case DBStructureManager::ColumnTypeEnum:
					$length = 0;
					foreach($type->values as $val) {
						$len = strlen($val);
						$length = $len > $length ? $len : $length;
					}
					$out = "varchar({$length})";
					break;
				case DBStructureManager::ColumnTypeFloat:
					$out = "float({$type->precision}) ";
					break;
				case DBStructureManager::ColumnTypeText:
					$out = "text ";
					break;
				case DBStructureManager::ColumnTypeVarchar:
					$out = "varchar({$type->precision})";
					break;
				case DBStructureManager::ColumnTypeTimestamp:
					$out = "timestamp";
					break;
				case DBStructureManager::ColumnTypeInt:
					$out = "int({$type->precision})";
					break;
				default:
					break;
			}
		}

		return $out;
	}
	protected function buildFullColumnType($spec, $includeName = true) {
		$out = "";

		if($includeName) {
			$out = "{$spec->fullname} {$this->buildColumnType($spec->type, $spec->autoincrement)} ";
		} else {
			$out = "{$this->buildColumnType($spec->type, $spec->autoincrement)} ";
		}
#		if(in_array($spec->type->type, array(DBStructureManager::ColumnTypeVarchar))) {
#			$out.= "collate utf8_bin ";
#		}
		if(!$spec->null) {
			$out.= "not null ";
		}
		if($spec->hasDefault) {
			if($spec->default === null) {
				if($spec->null) {
					$out.= "default null ";
				}
			} else {
				if(in_array($spec->type->type, array(DBStructureManager::ColumnTypeText, DBStructureManager::ColumnTypeVarchar))) {
					$out.= "default '{$spec->default}' ";
				} else {
					$out.= "default {$spec->default} ";
				}
			}
		}
		if($spec->autoincrement) {
			/** @fixme this is somehow impolite @{ */
			if($includeName) {
				$out.= "primary key autoincrement ";
			} else {
				$out.= "autoincrement ";
			}
			/** @} */
		}
#		if($spec->comment) {
#			$out.= "comment '".str_replace("'", "", $spec->comment)."' ";
#		} else {
#			$out.= "comment '' ";
#		}

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
