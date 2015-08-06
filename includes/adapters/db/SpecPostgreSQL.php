<?php

/**
 * @file SpecPostgreSQL.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

use TooBasic\Managers\DBStructureManager;

/**
 * @class SpecPostgreSQL
 */
class SpecPostgreSQL extends SpecAdapter {
	//
	// Protected properties.
	protected $_engine = 'PostgreSQL';
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

		$query = "select  t.relname as table_name, \n";
		$query.= "        i.relname as index_name, \n";
		$query.= "        a.attname as column_name \n";
		$query.= "from    pg_class t, \n";
		$query.= "        pg_class i, \n";
		$query.= "        pg_index ix, \n";
		$query.= "        pg_attribute a \n";
		$query.= "where   t.oid = ix.indrelid \n";
		$query.= " and    i.oid = ix.indexrelid \n";
		$query.= " and    a.attrelid = t.oid \n";
		$query.= " and    a.attnum = ANY(ix.indkey) \n";
		$query.= " and    t.relkind = 'r' \n";
		$query.= " and    ix.indisprimary = 'f' \n";
		$query.= " and    i.relname = '{$index->fullname}' \n";

		$indexSpecs = $this->_db->queryData($query);

		$dbColumnCount = count($indexSpecs);
		$spectColumnCount = count($index->fields);

		if($dbColumnCount == $spectColumnCount) {
			for($position = 0; $position < $dbColumnCount; $position++) {
				if($indexSpecs[$position]['column_name'] != $index->fields[$position]) {
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
		$tableSpecs = $this->_db->queryData("select * from information_schema.columns where table_schema = current_schema() and table_name = '{$table->fullname}'");
		$autoIncrement = count($this->_db->queryData("select column_default from information_schema.columns where table_schema = current_schema() and table_name = '{$table->fullname}' and column_default like 'nextval(''{$table->fullname}_%_seq''::regclass)'")) > 0;
		//
		// New columns.
		$cmp = array();
		foreach($table->fields as $fullname => $field) {
			$found = false;
			foreach($tableSpecs as $dbColumn) {
				if($fullname == $dbColumn['column_name']) {
					$found = $dbColumn;
					continue;
				}
			}
			if(!$found) {
				$creates[] = $fullname;
			} else {
				$cmp[$fullname] = array(
					GC_AFIELD_DB => $found,
					GC_AFIELD_SPEC => null
				);
			}
		}
		//
		// Old columns.
		foreach($tableSpecs as $dbColumn) {
			if(!isset($table->fields[$dbColumn['column_name']])) {
				$drops[] = $dbColumn['column_name'];
			} else {
				$spec = $table->fields[$dbColumn['column_name']];
				$spec->builtType = $this->buildColumnType($spec->type, $spec->autoincrement);
				$cmp[$dbColumn['column_name']][GC_AFIELD_SPEC] = $spec;
			}
		}
		//
		// Different columns.
		foreach($cmp as $fullname => $data) {
			if($data[GC_AFIELD_SPEC]->autoincrement && !$autoIncrement) {
				$updates[] = $fullname;
				continue;
			}
			if(!$this->compareColumnType($data)) {
				$updates[] = $fullname;
				continue;
			}
			if($data[GC_AFIELD_SPEC]->null != ($data[GC_AFIELD_DB]['is_nullable'] != 'NO')) {
				$updates[] = $fullname;
				continue;
			}
			if($data[GC_AFIELD_SPEC]->hasDefault) {
				$dbDefaultValue = explode('::', $data[GC_AFIELD_DB]['column_default']);
				$dbDefaultValue = pg_unescape_bytea(trim(array_shift($dbDefaultValue), "'"));
				$specDefaultValue = str_replace("'", '', $data[GC_AFIELD_SPEC]->default);

				switch($specDefaultValue) {
					case 'CURRENT_TIMESTAMP':
						$specDefaultValue = 'now()';
						break;
				}

				if(($data[GC_AFIELD_SPEC]->default === null && $dbDefaultValue != 'null') || $specDefaultValue != $dbDefaultValue) {
					$updates[] = $fullname;
					continue;
				}
			} elseif(!$data[GC_AFIELD_SPEC]->autoincrement && $data[GC_AFIELD_DB]['column_default']) {
				$updates[] = $fullname;
				continue;
			}
#			if($data[GC_AFIELD_DB]['column_comment'] != $data[GC_AFIELD_SPEC]->comment) {
#				$updates[] = $fullname;
#				continue;
#			}
		}
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

		return $this->exec($query);
	}
	public function createTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        add column {$field->fullname} {$this->buildFullColumnType($field, false, true)}";

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
		$query = "alter table {$table->fullname} \n";
		$query.= "        drop column {$columnName}";

		return $this->exec($query);
	}
	public function getIndexes() {
		$out = array();

		$query = "select  relname as name \n";
		$query.= "from    pg_class, \n";
		$query.= "        pg_index \n";
		$query.= "where   pg_class.oid = pg_index.indexrelid \n";
		$query.= " and    pg_class.oid in ( \n";
		$query.= "        select  indexrelid \n";
		$query.= "        from    pg_index, \n";
		$query.= "                pg_class \n";
		$query.= "        where  pg_class.oid =  pg_index.indrelid \n";
		$query.= "         and indisprimary   != 't' \n";
		$query.= "         and relname        !~ '^pg_' \n";
		$query.= "         and relname        !~ '^uk_') \n";
		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row['name'];
		}

		return $out;
	}
	public function getTables() {
		$out = array();

		$query = "select  tablename as name \n";
		$query.= "from    pg_catalog.pg_tables \n";
		$query.= "where   schemaname = current_schema() \n";

		foreach($this->_db->queryData($query, false) as $row) {
			$out[] = $row['name'];
		}

		return $out;
	}
	public function indexExists($indexName) {
		$query = "select  relname as name \n";
		$query.= "from    pg_class, \n";
		$query.= "        pg_index \n";
		$query.= "where   pg_class.oid = pg_index.indexrelid \n";
		$query.= " and    pg_class.oid in ( \n";
		$query.= "        select  indexrelid \n";
		$query.= "        from    pg_index, \n";
		$query.= "                pg_class \n";
		$query.= "        where  pg_class.oid =  pg_index.indrelid \n";
		$query.= "         and indisprimary   != 't' \n";
		$query.= "         and relname        !~ '{$indexName}') \n";

		return count($this->_db->queryData($query, false)) > 0;
	}
	public function tableExists($tableName) {
		$query = "select  * \n";
		$query.= "from    pg_class \n";
		$query.= "where   relname = '{$tableName}'";

		return count($this->_db->queryData($query, false)) == 1;
	}
	public function updateIndex(\stdClass $index) {
		$out = false;
		$knwonIndexes = $this->getIndexes();
		if(in_array($index->fullname, $knwonIndexes)) {
			$out = $this->dropIndex($index->fullname) && $this->createIndex($index);
		} else {
			$out = $this->createIndex($index);
		}
		return $out;
	}
	public function updateTableColumn(\stdClass $table, $columnName) {
		$query = "alter table {$table->fullname} \n";

		$field = $table->fields[$columnName];
		$query.= "        alter column {$field->fullname} {$this->buildFullColumnType($field, false, true)}";

		return $this->exec($query);
	}
	//
	// Protected methods.
	protected function buildColumnType($type, $isAutoincrement, $isAlter = false) {
		$out = '';

		if($isAutoincrement) {
			$out = 'serial';
		} else {
			switch($type->type) {
				case DBStructureManager::ColumnTypeBlob:
					$out = 'bytea';
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
					$out = "float({$type->precision})";
					break;
				case DBStructureManager::ColumnTypeText:
					$out = 'text';
					break;
				case DBStructureManager::ColumnTypeVarchar:
					$out = "varchar({$type->precision})";
					break;
				case DBStructureManager::ColumnTypeTimestamp:
					$out = 'timestamp';
					break;
				case DBStructureManager::ColumnTypeInt:
					$out = "numeric({$type->precision})";
					break;
				default:
					break;
			}
			if($isAlter) {
				$out = "type {$out}";
			}
		}

		return $out;
	}
	protected function buildFullColumnType($spec, $includeName = true, $isAlter = false) {
		$out = '';

		if($includeName) {
			$out = "{$spec->fullname} {$this->buildColumnType($spec->type, $spec->autoincrement, $isAlter)} ";
		} else {
			$out = "{$this->buildColumnType($spec->type, $spec->autoincrement, $isAlter)} ";
		}
#		if(in_array($spec->type->type, array(DBStructureManager::ColumnTypeVarchar))) {
#			$out.= 'collate utf8_bin ';
#		}
		if(!$spec->null) {
			if($isAlter) {
				$out.= ", alter column {$spec->fullname} set not null ";
			} else {
				$out.= 'not null ';
			}
		} elseif($isAlter) {
			$out.= ", alter column {$spec->fullname} drop not null ";
		}
		if(!$spec->autoincrement) {
			if($spec->hasDefault) {
				if($isAlter) {
					$out.= ", alter column {$spec->fullname} set ";
				}
				if($spec->default === null) {
					if($spec->null) {
						$out.= 'default null ';
					}
				} else {
					if(in_array($spec->type->type, array(DBStructureManager::ColumnTypeBlob, DBStructureManager::ColumnTypeText, DBStructureManager::ColumnTypeVarchar))) {
						$out.= "default '{$spec->default}' ";
					} else {
						$out.= "default {$spec->default} ";
					}
				}
			}
		}
		if($spec->autoincrement) {
			/** @fixme this is somehow impolite @{ */
			if($includeName) {
				$out.= 'primary key ';
			}
			/** @} */
		}
#			if($spec->comment) {
#				$out.= "comment '".str_replace("'", '', $spec->comment)."' ";
#			} else {
#				$out.= "comment '' ";
#		}

		return $out;
	}
	protected function compareColumnType($data) {
		$same = true;

		if($data[GC_AFIELD_SPEC]->autoincrement) {
			if($data[GC_AFIELD_DB]['data_type'] != 'integer') {
				$same = false;
			} elseif($data[GC_AFIELD_DB]['column_default'] != "nextval('{$data[GC_AFIELD_DB]['table_name']}_{$data[GC_AFIELD_DB]['column_name']}_seq'::regclass)") {
				$same = false;
			}
		} else {
			switch($data[GC_AFIELD_SPEC]->type->type) {
				case DBStructureManager::ColumnTypeInt:
					if($data[GC_AFIELD_DB]['udt_name'] != 'numeric') {
						$same = false;
					} elseif($data[GC_AFIELD_DB]['numeric_precision'] != $data[GC_AFIELD_SPEC]->type->precision) {
						$same = false;
					}
					break;
				case DBStructureManager::ColumnTypeEnum:
					if($data[GC_AFIELD_DB]['udt_name'] != 'varchar') {
						$same = false;
					} else {
						$length = 0;
						foreach($data[GC_AFIELD_SPEC]->type->values as $val) {
							$len = strlen($val);
							$length = $len > $length ? $len : $length;
						}
						if($data[GC_AFIELD_DB]['character_maximum_length'] != $length) {
							$same = false;
						}
					}
					break;
				case DBStructureManager::ColumnTypeFloat:
					if($data[GC_AFIELD_DB]['udt_name'] != 'float8' && $data[GC_AFIELD_DB]['udt_name'] != 'float4') {
						$same = false;
					}
					break;
				case DBStructureManager::ColumnTypeBlob:
					//
					// Non-precision types.
					if($data[GC_AFIELD_DB]['udt_name'] != 'bytea') {
						debugit($data[GC_AFIELD_DB]['udt_name'], 1);
						$same = false;
					}
					break;
				case DBStructureManager::ColumnTypeText:
				case DBStructureManager::ColumnTypeTimestamp:
					//
					// Non-precision types.
					if($data[GC_AFIELD_DB]['udt_name'] != $data[GC_AFIELD_SPEC]->type->type) {
						$same = false;
					}
					break;
				default:
					//
					// Basic types.
					if($data[GC_AFIELD_DB]['udt_name'] != $data[GC_AFIELD_SPEC]->type->type) {
						$same = false;
					} elseif($data[GC_AFIELD_DB]['character_maximum_length'] != $data[GC_AFIELD_SPEC]->type->precision) {
						$same = false;
					}
			}
		}

		return $same;
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
