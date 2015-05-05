<?php

namespace TooBasic;

class DBStructureManagerExeption extends \Exception {
	
}

class DBStructureManager extends Manager {
	//
	// Constants.
	const ErrorOk = 0;
	const ErrorDefault = 1;
	const ErrorUnknownTable = 2;
	const ErrorUnknownType = 3;
	const ErrorUnknownConnection = 4;
	const ColumnTypeBlob = "blob";
	const ColumnTypeEnum = "enum";
	const ColumnTypeFloat = "float";
	const ColumnTypeInt = "int";
	const ColumnTypeText = "text";
	const ColumnTypeTimestamp = "timestamp";
	const ColumnTypeVarchar = "varchar";
	const TaskTypeCreateColumn = "create-column";
	const TaskTypeCreateIndex = "create-indexes";
	const TaskTypeCreateTable = "create-tables";
	const TaskTypeDropColumn = "drop-column";
	const TaskTypeDropIndex = "drop-indexes";
	const TaskTypeDropTable = "drop-tables";
	const TaskTypeUpdateColumn = "update-column";
	const TaskStatus = "status";
	// 
	// Protected class properties.
	protected static $_AllowedColumnTypes = array(
		self::ColumnTypeBlob,
		self::ColumnTypeEnum,
		self::ColumnTypeFloat,
		self::ColumnTypeInt,
		self::ColumnTypeText,
		self::ColumnTypeTimestamp,
		self::ColumnTypeVarchar
	);
	// 
	// Protected properties.
	/**
	 * @var \TooBasic\DBAdapter[string] 
	 */
	protected $_dbAdapters = array();
	/**
	 * @var \TooBasic\DBManager
	 */
	protected $_dbManager = false;
	protected $_errors = array();
	protected $_installed = false;
	protected $_perConnection = array();
	protected $_specFiles = array();
	protected $_specs = false;
	protected $_tasks = false;
	//
	// Public methods.
	public function check() {
		$ok = false;

		if(!$this->_installed && !$this->hasErrors()) {
			if($this->_tasks !== false) {
				$ok = $this->_tasks[self::TaskStatus];
			} else {
				$ok = true;

				$this->_tasks = array(
					self::TaskTypeCreateIndex => array(),
					self::TaskTypeCreateColumn => array(),
					self::TaskTypeCreateTable => array(),
					self::TaskTypeDropColumn => array(),
					self::TaskTypeDropIndex => array(),
					self::TaskTypeDropTable => array(),
					self::TaskTypeUpdateColumn => array()
				);
				//
				// Checking tables existence.
				foreach($this->_specs->tables as $tKey => $table) {
					$adapter = $this->getAdapter($table->connection);

					if(!$adapter->tableExists($table->fullname)) {
						$this->_tasks[self::TaskTypeCreateTable][] = $tKey;
						$ok = false;
					} else {
						// 
						// Check columns.
						$creates = array();
						$drops = array();
						$updates = array();
						$adapter->compareTable($table, $creates, $drops, $updates);
						if($creates) {
							$this->_tasks[self::TaskTypeCreateColumn][$tKey] = $creates;
							$ok = false;
						}
						if(!$adapter->keepUnknowns() && $drops) {
							$this->_tasks[self::TaskTypeDropColumn][$tKey] = $drops;
							$ok = false;
						}
						if($updates) {
							$this->_tasks[self::TaskTypeUpdateColumn][$tKey] = $updates;
							$ok = false;
						}
					}
				}
				//
				// Checking indexes existence.
				foreach($this->_specs->indexes as $iKey => $index) {
					$adapter = $this->getAdapter($index->connection);

					if(!$adapter->indexExists($index->fullname)) {
						$this->_tasks[self::TaskTypeCreateIndex][] = $iKey;
						$ok = false;
					}
				}
				if(!$this->_dbManager->keepUnknowns()) {
					foreach($this->_perConnection as $connName => $connection) {
						$adapter = $this->getAdapter($connName);
						foreach($adapter->getIndexes() as $dbIndex) {
							if(!in_array($dbIndex, $connection["indexes"])) {
								$this->_tasks[self::TaskTypeDropIndex][] = array(
									"connection" => $connName,
									"name" => $dbIndex
								);
								$ok = false;
							}
						}
						foreach($adapter->getTables() as $dbTable) {
							if(!in_array($dbTable, $connection["tables"])) {
								$this->_tasks[self::TaskTypeDropTable][] = array(
									"connection" => $connName,
									"name" => $dbTable
								);
								$ok = false;
							}
						}
					}
				}

				$this->_tasks[self::TaskStatus] = $ok;
			}
		} else {
			$ok = true;
		}

		return $ok;
	}
	public function errors() {
		return $this->_errors;
	}
	public function hasErrors() {
		return boolval($this->_errors);
	}
	public function specs() {
		return $this->_specs;
	}
	public function tasks() {
		$this->check();
		return $this->_tasks;
	}
	public function upgrade() {
		$out = true;

		if(!$this->check()) {
			$this->createTables();
			$this->createColumns();
			$this->updateColumns();

			$this->createIndexes();

			$this->dropColumns();
			$this->dropIndexes();
			$this->dropTables();

			if(isset(Params::Instance()->debugdbemulation)) {
				debugit("Database upgrade emulation", true);
			}

			$this->_tasks = false;
			$out = $this->check();
		}

		return $out;
	}
	//
	// Protected methods.
	protected function checkSpecs() {
		//
		// Checking indexes.
		foreach($this->_specs->indexes as $iKey => &$index) {
			$tKey = sha1("{$index->connection}-{$index->table}");
			if(!isset($this->_specs->tables[$tKey])) {
				$this->setError(self::ErrorUnknownTable, "Index '{$index->fullname}' uses an unknown table called '{$index->table}'");
				unset($this->_specs->indexes[$iKey]);
			} else {
				$table = $this->_specs->tables[$tKey];
				$index->table = $table->fullname;

				$prefix = $table->prefix;
				foreach($index->fields as &$field) {
					$field = "{$prefix}{$field}";
				}
			}
		}
	}
	protected function createColumns() {
		foreach($this->_tasks[self::TaskTypeCreateColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($columns as $column) {
				$adapter->createTableColumn($table, $column);
			}
		}
	}
	protected function createIndexes() {
		foreach($this->_tasks[self::TaskTypeCreateIndex] as $iKey) {
			$index = $this->_specs->indexes[$iKey];
			$adapter = $this->getAdapter($index->connection);
			$adapter->createIndex($index);
		}
	}
	protected function createTables() {
		foreach($this->_tasks[self::TaskTypeCreateTable] as $tKey) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			$adapter->createTable($table);
		}
	}
	protected function dropColumns() {
		foreach($this->_tasks[self::TaskTypeDropColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($columns as $column) {
				$adapter->dropTableColumn($table, $column);
			}
		}
	}
	protected function dropIndexes() {
		foreach($this->_tasks[self::TaskTypeDropIndex] as $data) {
			$adapter = $this->getAdapter($data["connection"]);
			$adapter->dropIndex($data["name"]);
		}
	}
	protected function dropTables() {
		foreach($this->_tasks[self::TaskTypeDropTable] as $data) {
			$adapter = $this->getAdapter($data["connection"]);
			$adapter->dropTable($data["name"]);
		}
	}
	protected function getAdapter($connectionName) {
		$out = false;

		if(!isset($this->_dbAdapters[$connectionName])) {
			global $Connections;
			global $Database;

			$engine = false;
			if(isset($Connections[GC_CONNECTIONS_DB][$connectionName]) && isset($Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE]) && isset($Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE])) {
				$engine = $Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE];
			} else {
				throw new DBStructureManagerExeption("Unable to obtain connection '{$connectionName}' configuration");
			}
			if(!isset($Database[GC_DATABASE_DB_ADAPTERS][$engine])) {
				throw new DBStructureManagerExeption("There's no adapter for engine '{$engine}'");
			}

			$db = DBManager::Instance()->{$connectionName};
			if($db) {
				$adapterName = $Database[GC_DATABASE_DB_ADAPTERS][$engine];
				$this->_dbAdapters[$connectionName] = new $adapterName($db);
				$out = $this->_dbAdapters[$connectionName];
			} else {
				throw new DBStructureManagerExeption("Unable to obtaing a connetion to '{$connectionName}'");
			}
		} else {
			$out = $this->_dbAdapters[$connectionName];
		}

		return $out;
	}
	protected function init() {
		parent::init();

		global $Defaults;
		$this->_installed = $Defaults[GC_DEFAULTS_INSTALLED];
		//
		// If the system is not set as installed, there are other
		// conditions that may stop this manager from cheking and
		// installing, these are:
		//	- no database connections.
		//	- no default database connection.
		//	- wrong default database connection.
		if(!$this->_installed) {
			global $Connections;
			//
			// No database connections.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB]) || !boolval($Connections[GC_CONNECTIONS_DB]))) {
				$this->_installed = true;
			}
			//
			// No default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]) || !$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB])) {
				$this->_installed = true;
			}
			// 
			// Wrong default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB][$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]]))) {
				$this->_installed = true;
			}
		}
		//
		// There's a way to force installation checks and upgrades, but
		// only from shell tools.
		if(defined("__SHELL__") && isset(Params::Instance()->debugforcedbinstall)) {
			$this->_installed = false;
		}
		//
		// If the system is not flagged as installed, it must load the
		// database specification and check it.
		if(!$this->_installed) {
			$this->_dbManager = DBManager::Instance();

			$this->loadSpecs();
			$this->parseSpecs();
			$this->checkSpecs();

			if(isset(Params::Instance()->debugdbstructure)) {
				debugit(array(
					"errors" => $this->_errors,
					"files" => $this->_specFiles,
					"specs" => $this->_specs
				));
			}
		}
	}
	protected function loadSpecs() {
		//
		// Adding configuration.
		$this->_specFiles = Paths::Instance()->dbSpecPaths();
		//
		// Adding default configuration.
		global $Database;
		array_unshift($this->_specFiles, $Database[GC_DATABASE_DEFAULT_SPECS]);
	}
	protected function parseSpec($path) {
		if(!$this->_specs) {
			$this->_specs = new \stdClass();
		}

		$json = json_decode(file_get_contents($path));
		if(!$json) {
			trigger_error("JSON spec at '{$path}' is broken. [".json_last_error()."] ".json_last_error_msg(), E_USER_ERROR);
		}

		$this->parseSpecConfigs(isset($json->configs) ? $json->configs : new \stdClass());
		$this->parseSpecTables(isset($json->tables) ? $json->tables : new \stdClass());
		$this->parseSpecIndexes(isset($json->indexes) ? $json->indexes : new \stdClass());
	}
	protected function parseSpecConfigs($configs) {
		//
		// Enforcing given config @{
		if(!isset($configs->prefixes)) {
			$configs->prefixes = new \stdClass();
		}
		// @}
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->configs)) {
			$this->_specs->configs = new \stdClass();
		}
		//
		// Required configs' fileds.
		foreach(array("prefixes") as $field) {
			if(!isset($this->_specs->configs->{$field})) {
				$this->_specs->configs->{$field} = new \stdClass();
			}
		}
		//
		// Loading prefixes.
		$this->_specs->configs->prefixes = self::CopyAndEnforce(array("index", "key", "primary"), $configs->prefixes, $this->_specs->configs->prefixes);
	}
	protected function parseSpecIndexes($indexes) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->indexes)) {
			$this->_specs->indexes = array();
		}

		global $Connections;

		foreach($indexes as $index) {
			$aux = self::CopyAndEnforce(array("name", "table", "type", "connection", "fields"), $index, new \stdClass(), array("fields" => array()));

			if(!$aux->fields) {
				continue;
			}

			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $this->_dbManager->getInstallName();
			}

			$prefix = "";
			if(isset($this->_specs->configs->prefixes->{$aux->type})) {
				$prefix = $this->_specs->configs->prefixes->{$aux->type};
			}
			$aux->fullname = "{$prefix}{$aux->name}";
			//
			// Accepting index spec.
			$key = sha1("{$aux->connection}-{$aux->fullname}");
			$this->_specs->indexes[$key] = $aux;
			if(!isset($this->_perConnection[$aux->connection])) {
				$this->_perConnection[$aux->connection] = array();
			}
			if(!isset($this->_perConnection[$aux->connection]["indexes"])) {
				$this->_perConnection[$aux->connection]["indexes"] = array();
			}
			$this->_perConnection[$aux->connection]["indexes"][] = $aux->fullname;
		}
	}
	protected function parseSpecs() {
		foreach($this->_specFiles as $path) {
			$this->parseSpec($path);
		}
		foreach($this->_perConnection as &$connection) {
			foreach($connection as &$type) {
				$type = array_unique($type);
			}
		}
	}
	protected function parseSpecTables($tables) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->tables)) {
			$this->_specs->tables = array();
		}

		global $Connections;

		foreach($tables as $table) {
			if(!$table->fields) {
				continue;
			}

			$aux = self::CopyAndEnforce(array("name", "connection", "prefix", "engine"), $table, new \stdClass());

			$aux->fields = array();
			foreach($table->fields as $field) {
				$auxField = self::CopyAndEnforce(array("name", "type", "autoincrement", "null", "comment"), $field, new \stdClass(), array("autoincrement" => false, "null" => false));
				$auxField->fullname = "{$aux->prefix}{$auxField->name}";

				if(!isset($auxField->type->type)) {
					$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' has no type");
					continue;
				} elseif(!in_array($auxField->type->type, self::$_AllowedColumnTypes)) {
					$this->setError(self::ErrorUnknownType, "Unknown field type '{$auxField->type->type}' for field '{$auxField->fullname}' on table '{$aux->name}'");
					continue;
				}
				if(!isset($auxField->type->precision)) {
					if($auxField->type->type == self::ColumnTypeEnum && !isset($auxField->type->values)) {
						$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' is enumerative and has no value");
						continue;
					} elseif($auxField->type->type != self::ColumnTypeEnum) {
						$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' has no precision");
						continue;
					}
				}
				if(isset($field->default)) {
					$auxField->default = $field->default;
					$auxField->hasDefault = true;
				} else {
					$auxField->hasDefault = false;
				}

				$aux->fields[$auxField->fullname] = $auxField;
			}

			if(!$aux->fields) {
				continue;
			}
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $this->_dbManager->getInstallName();
			}

			$prefix = "";
			if(isset($Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX])) {
				$prefix = $Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX];
			}
			$aux->fullname = "{$prefix}{$aux->name}";
			//
			// Acception table specs.
			$key = sha1("{$aux->connection}-{$aux->name}");
			if(!isset($this->_specs->tables[$key])) {
				$this->_specs->tables[$key] = $aux;
			} else {
				if($this->_specs->tables[$key]->prefix == $aux->prefix) {
					foreach($aux->fields as $field) {
						$this->_specs->tables[$key]->fields[$field->fullname] = $field;
					}
				}
			}
			if(!isset($this->_perConnection[$aux->connection])) {
				$this->_perConnection[$aux->connection] = array();
			}
			if(!isset($this->_perConnection[$aux->connection]["tables"])) {
				$this->_perConnection[$aux->connection]["tables"] = array();
			}
			$this->_perConnection[$aux->connection]["tables"][] = $aux->fullname;
		}
	}
	protected function setError($code, $message) {
		$this->_errors[] = array(
			"code" => $code,
			"message" => $message
		);
	}
	protected function updateColumns() {
		foreach($this->_tasks[self::TaskTypeUpdateColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($columns as $column) {
				$adapter->updateTableColumn($table, $column);
			}
		}
	}
	//
	// Protected class methods.
	protected static function CopyAndEnforce($fields, \stdClass $origin, \stdClass $destination, $defualt = array()) {
		if(!is_array($defualt)) {
			$defualt = array();
		}

		foreach($fields as $field) {
			if(isset($origin->{$field})) {
				$destination->{$field} = $origin->{$field};
			} elseif(!isset($destination->{$field})) {
				$destination->{$field} = isset($defualt[$field]) ? $defualt[$field] : "";
			}
		}

		return $destination;
	}
}
