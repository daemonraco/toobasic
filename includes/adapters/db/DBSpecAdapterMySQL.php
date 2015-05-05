<?php

namespace TooBasic;

class DBSpecAdapterMySQL extends DBSpecAdapter {
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
		$out = false;

		$query = "select  * \n";
		$query.= "from    {$table->fullname} \n";
		$query.= "where   1 = 1 \n";
		foreach($entry->check as $field) {
			$query.= " and    {$field} = '{$entry->entry->$field}' \n";
		}

		$result = $this->_db->query($query, false);
		if($result) {
			$out = $result->rowCount() > 0;
		}

		return $out;
	}
	public function compareTable(\stdClass $table, &$creates, &$drops, &$updates) {
		$query = "select  column_name, \n";
		$query.= "        column_default, \n";
		$query.= "        is_nullable, \n";
		$query.= "        data_type, \n";
		$query.= "        character_maximum_length, \n";
		$query.= "        character_octet_length, \n";
		$query.= "        numeric_precision, \n";
		$query.= "        numeric_scale, \n";
		$query.= "        column_type, \n";
		$query.= "        extra, \n";
		$query.= "        column_comment \n";
		$query.= "from    information_schema.columns \n";
		$query.= "where   table_catalog = 'def' \n";
		$query.= " and    table_schema  = database() \n";
		$query.= " and    table_name    = '{$table->fullname}'";

		$tableSpecs = $this->_db->query($query)->fetchAll();
		//
		// New columns.
		$cmp = array();
		foreach($table->fields as $fullname => $field) {
			$found = false;
			foreach($tableSpecs as $dbColumn) {
				if($fullname == $dbColumn["column_name"]) {
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
			if(!isset($table->fields[$dbColumn["column_name"]])) {
				$drops[] = $dbColumn["column_name"];
			} else {
				$spec = $table->fields[$dbColumn["column_name"]];
				$spec->builtType = $this->buildColumnType($spec->type);
				$cmp[$dbColumn["column_name"]]["spec"] = $spec;
			}
		}
		//
		// Different columns.
		foreach($cmp as $fullname => $data) {
			if($data["db"]["column_type"] != $data["spec"]->builtType) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->autoincrement != ($data["db"]["extra"] == "auto_increment")) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->null != ($data["db"]["is_nullable"] == "YES")) {
				$updates[] = $fullname;
				continue;
			}
			if($data["spec"]->hasDefault) {
				if(($data["spec"]->default === null && $data["db"]["column_default"] != "NULL") || $data["spec"]->default != $data["db"]["column_default"]) {
					$updates[] = $fullname;
					continue;
				}
			} elseif($data["db"]["column_default"]) {
				$updates[] = $fullname;
				continue;
			}
			if($data["db"]["column_comment"] != $data["spec"]->comment) {
				$updates[] = $fullname;
				continue;
			}
		}
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
		if(isset($table->engine)) {
			$query.= "engine={$table->engine} ";
		}
		$query.= " default charset=utf8 collate=utf8_bin auto_increment=1 ";

		return $this->exec($query);
	}
	public function createTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        add column {$field->fullname} {$this->buildFullColumnType($field, false)}";

		return $this->exec($query);
	}
	public function dropIndex($indexName) {
		$out = false;

		$query = "select  distinct table_name as 'table' \n";
		$query.= "from    information_schema.statistics \n";
		$query.= "where   table_catalog = 'def' \n";
		$query.= " and    table_schema  = database() \n";
		$query.= " and    index_name    = '{$indexName}' \n";

		$result = $this->_db->query($query, false);
		if($result) {
			$row = $result->fetch();

			$query = "drop index {$indexName} on {$row["table"]}";
			$out = $this->exec($query);
		}

		return $out;
	}
	public function dropTable($tableName) {
		$query = "drop table {$tableName}";
		return $this->exec($query);
	}
	public function dropTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";
		$query.= "        drop column {$columnName}";

		return $this->exec($query);
	}
	public function getIndexes() {
		$out = array();

		$query = "select  distinct index_name as name \n";
		$query.= "from    information_schema.statistics \n";
		$query.= "where   table_catalog =  'def' \n";
		$query.= " and    table_schema  =  database() \n";
		$query.= " and    index_name    <> 'PRIMARY' \n";

		$result = $this->_db->query($query, false);
		if($result) {
			foreach($result->fetchAll() as $row) {
				$out[] = $row["name"];
			}
		}

		return $out;
	}
	public function getTables() {
		$out = array();

		$query = "select  distinct(table_name) as name \n";
		$query.= "from    information_schema.statistics \n";
		$query.= "where   table_catalog = 'def' \n";
		$query.= " and    table_schema  = database()";

		$result = $this->_db->query($query, false);
		if($result) {
			foreach($result->fetchAll() as $row) {
				$out[] = $row["name"];
			}
		}

		return $out;
	}
	public function indexExists($indexName) {
		$out = false;

		$query = "select  index_name \n";
		$query.= "from    information_schema.statistics \n";
		$query.= "where   table_catalog = 'def' \n";
		$query.= " and    table_schema  = database() \n";
		$query.= " and    index_name    = '{$indexName}'";

		$result = $this->_db->query($query, false);
		if($result) {
			$out = $result->rowCount() > 0;
		}

		return $out;
	}
	public function tableExists($tableName) {
		return $this->_db->query("select 1 from {$tableName}", false) !== false;
	}
	public function updateTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        modify column {$field->fullname} {$this->buildFullColumnType($field, false)}";

		return $this->exec($query);
	}
	//
	// Protected methods.
	protected function buildColumnType($type) {
		$out = "";

		switch($type->type) {
			case DBStructureManager::ColumnTypeBlob:
				$out = "blob";
				break;
			case DBStructureManager::ColumnTypeEnum:
				$out = "enum('".implode("','", $type->values)."')";
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

		return $out;
	}
	protected function buildFullColumnType($spec, $includeName = true) {
		$out = "";

		if($includeName) {
			$out = "{$spec->fullname} {$this->buildColumnType($spec->type)} ";
		} else {
			$out = "{$this->buildColumnType($spec->type)} ";
		}
		if(in_array($spec->type->type, array(DBStructureManager::ColumnTypeVarchar))) {
			$out.= "collate utf8_bin ";
		}
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
			/** @fixme this is some kind impolite @{ */
			if($includeName) {
				$out.= "primary key auto_increment ";
			} else {
				$out.= "auto_increment ";
			}
			/** @} */
		}
		if($spec->comment) {
			$out.= "comment '".str_replace("'", "", $spec->comment)."' ";
		} else {
			$out.= "comment '' ";
		}

		return $out;
	}
}
