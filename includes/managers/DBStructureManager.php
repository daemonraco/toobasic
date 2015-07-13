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
	const ErrorUnknownCallback = 5;
	const ColumnTypeBlob = 'blob';
	const ColumnTypeEnum = 'enum';
	const ColumnTypeFloat = 'float';
	const ColumnTypeInt = 'int';
	const ColumnTypeText = 'text';
	const ColumnTypeTimestamp = 'timestamp';
	const ColumnTypeVarchar = 'varchar';
	const TaskTypeCreateColumn = 'create-column';
	const TaskTypeCreateIndex = 'create-indexes';
	const TaskTypeCreateTable = 'create-tables';
	const TaskTypeDropColumn = 'drop-column';
	const TaskTypeDropIndex = 'drop-indexes';
	const TaskTypeDropTable = 'drop-tables';
	const TaskTypeUpdateColumn = 'update-column';
	const TaskTypeUpdateData = 'update-data';
	const TaskTypeUpdateIndex = 'update-index';
	const TaskStatus = 'status';
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
	protected static $_ColumnTypesWithoutPrecisions = array(
		self::ColumnTypeBlob,
		self::ColumnTypeEnum,
		self::ColumnTypeText,
		self::ColumnTypeTimestamp,
	);
	// 
	// Protected properties.
	protected $_callbacks = array();
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
					self::TaskTypeUpdateColumn => array(),
					self::TaskTypeUpdateData => array(),
					self::TaskTypeUpdateIndex => array()
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
				// Checking indexes existence and structure.
				foreach($this->_specs->indexes as $iKey => $index) {
					$adapter = $this->getAdapter($index->connection);

					if(!$adapter->indexExists($index->fullname)) {
						$this->_tasks[self::TaskTypeCreateIndex][] = $iKey;
						$ok = false;
					} else {
						if(!$adapter->compareIndex($index)) {
							$this->_tasks[self::TaskTypeUpdateIndex][] = $iKey;
							$ok = false;
						}
					}
				}
				//
				// Checking data existence.
				foreach($this->_specs->data as $tKey => $entries) {
					$table = $this->_specs->tables[$tKey];
					$adapter = $this->getAdapter($table->connection);

					$this->_tasks[self::TaskTypeUpdateData][$tKey] = array();
					foreach($entries as $eKey => $entry) {
						if(!$adapter->checkTableEntry($table, $entry)) {
							$this->_tasks[self::TaskTypeUpdateData][$tKey][] = $eKey;
							$ok = false;
						}
					}
				}
				foreach($this->_tasks[self::TaskTypeUpdateData] as $tKey => $eKeys) {
					if(!$eKeys) {
						unset($this->_tasks[self::TaskTypeUpdateData][$tKey]);
					}
				}
				//
				//
				if(!$this->_dbManager->keepUnknowns()) {
					foreach($this->_perConnection as $connName => $connection) {
						$adapter = $this->getAdapter($connName);
						foreach($adapter->getIndexes() as $dbIndex) {
							if(!in_array($dbIndex, $connection['indexes'])) {
								$this->_tasks[self::TaskTypeDropIndex][] = array(
									'connection' => $connName,
									'name' => $dbIndex
								);
								$ok = false;
							}
						}
						foreach($adapter->getTables() as $dbTable) {
							if(!in_array($dbTable, $connection['tables'])) {
								$this->_tasks[self::TaskTypeDropTable][] = array(
									'connection' => $connName,
									'name' => $dbTable
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
		return \boolval($this->_errors);
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

			$this->insertData();
			$this->createIndexes();
			$this->updateIndexes();

			$this->dropColumns();
			$this->dropIndexes();
			$this->dropTables();

			if(isset(Params::Instance()->debugdbemulation)) {
				\TooBasic\debugThing('Database upgrade emulation');
				die;
			}

			$this->_tasks = false;
			$out = $this->check();
		}

		return $out;
	}
	//
	// Protected methods.
	protected function checkCallbacks() {
		foreach($this->_callbacks as &$subCallbacks) {
			foreach($subCallbacks as &$data) {
				$data['path'] = Paths::Instance()->dbSpecCallbackPaths($data['name']);
				if(!$data['path']) {
					$this->setError(self::ErrorUnknownCallback, "Unable to find database spec callback '{$data['name']}'");
				}
			}
		}
		//
		// Cleaning table callbacks.
		foreach($this->_specs->tables as &$table) {
			$table->realCallbacks = new \stdClass();
			foreach($table->callbacks as $callbackType => $keys) {
				$table->realCallbacks->{$callbackType} = $keys ? $keys[0] : false;
			}
			unset($table->callbacks);
			//
			// Cleaning table column callbacks.
			foreach($table->fields as &$field) {
				$field->realCallbacks = new \stdClass();
				foreach($field->callbacks as $callbackType => $keys) {
					$field->realCallbacks->{$callbackType} = $keys ? $keys[0] : false;
				}
				unset($field->callbacks);
			}
		}
		//
		// Cleaning index callbacks.
		foreach($this->_specs->indexes as &$index) {
			$index->realCallbacks = new \stdClass();
			foreach($index->callbacks as $callbackType => $keys) {
				$index->realCallbacks->{$callbackType} = $keys ? $keys[0] : false;
			}
			unset($index->callbacks);
		}
	}
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
		//
		// Checking data.
		foreach($this->_specs->data as $tKey => &$entries) {
			if(!isset($this->_specs->tables[$tKey])) {
				unset($this->_specs->data[$tKey]);
			} else {
				$table = $this->_specs->tables[$tKey];
				$prefix = $table->prefix;
				foreach($entries as &$entry) {
					foreach($entry->check as &$cfield) {
						$cfield = "{$prefix}{$cfield}";
					}

					$newEntry = new \stdClass();
					foreach($entry->entry as $efield => $value) {
						$name = "{$prefix}{$efield}";
						$newEntry->{$name} = $value;
					}
					$entry->entry = $newEntry;
				}
			}
		}
	}
	protected function createColumns() {
		foreach($this->_tasks[self::TaskTypeCreateColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($columns as $column) {
				$callbackKeyBefore = "F_before_create_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_create_{$tKey}_{$column}";

				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				$adapter->createTableColumn($table, $column);
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	protected function createIndexes() {
		foreach($this->_tasks[self::TaskTypeCreateIndex] as $iKey) {
			$index = $this->_specs->indexes[$iKey];
			$adapter = $this->getAdapter($index->connection);

			$callbackKeyBefore = "I_before_create_{$iKey}";
			$callbackKeyAfter = "I_after_create_{$iKey}";

			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			$adapter->createIndex($index);
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	protected function createTables() {
		foreach($this->_tasks[self::TaskTypeCreateTable] as $tKey) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);

			$callbackKeyBefore = "T_before_create_{$tKey}";
			$callbackKeyAfter = "T_after_create_{$tKey}";

			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			$adapter->createTable($table);
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	protected function dropColumns() {
		foreach($this->_tasks[self::TaskTypeDropColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);

			foreach($columns as $column) {
				$callbackKeyBefore = "F_before_drop_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_drop_{$tKey}_{$column}";

				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				$adapter->dropTableColumn($table, $column);
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	protected function dropIndexes() {
		foreach($this->_tasks[self::TaskTypeDropIndex] as $data) {
			$adapter = $this->getAdapter($data['connection']);
			$callbackKeyBefore = "I_before_drop_{$data['name']}";
			$callbackKeyAfter = "I_after_drop_{$data['name']}";

			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			$adapter->dropIndex($data['name']);
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	protected function dropTables() {
		foreach($this->_tasks[self::TaskTypeDropTable] as $data) {
			$callbackKeyBefore = "T_before_drop_{$data['name']}";
			$callbackKeyAfter = "T_after_drop_{$data['name']}";
			$adapter = $this->getAdapter($data['connection']);

			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			$adapter->dropTable($data['name']);
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
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
			if(!isset($Database[GC_DATABASE_DB_SPEC_ADAPTERS][$engine])) {
				throw new DBStructureManagerExeption("There's no adapter for engine '{$engine}'");
			}

			$db = DBManager::Instance()->{$connectionName};
			if($db) {
				$adapterName = $Database[GC_DATABASE_DB_SPEC_ADAPTERS][$engine];
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
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB]) || !\boolval($Connections[GC_CONNECTIONS_DB]))) {
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
		if(defined('__SHELL__') && isset(Params::Instance()->debugforcedbinstall)) {
			$this->_installed = false;
		}
		//
		// If the system is not flagged as installed, it must load the
		// database specification and check it.
		if(!$this->_installed) {
			$this->_dbManager = DBManager::Instance();

			$this->loadSpecs();
			$this->parseSpecs();
			$this->checkCallbacks();
			$this->checkSpecs();

			if(isset(Params::Instance()->debugdbstructure)) {
				\TooBasic\debugThing(array(
					'errors' => $this->_errors,
					'files' => $this->_specFiles,
					'specs' => $this->_specs,
					'callbacks' => $this->_callbacks
				));
			}
		}
	}
	protected function insertData() {
		foreach($this->_tasks[self::TaskTypeUpdateData] as $tKey => $eKeys) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($eKeys as $eKey) {
				$entry = $this->_specs->data[$tKey][$eKey];
				$adapter->addTableEntry($table, $entry);
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
			throw new Exception("JSON spec at '{$path}' is broken. [".json_last_error().'] '.json_last_error_msg());
		}

		$this->parseSpecConfigs(isset($json->configs) ? $json->configs : new \stdClass());
		$this->parseSpecTables(isset($json->tables) ? $json->tables : array());
		$this->parseSpecIndexes(isset($json->indexes) ? $json->indexes : array());
		$this->parseSpecData(isset($json->data) ? $json->data : array());
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
		foreach(array('prefixes') as $field) {
			if(!isset($this->_specs->configs->{$field})) {
				$this->_specs->configs->{$field} = new \stdClass();
			}
		}
		//
		// Loading prefixes.
		$this->_specs->configs->prefixes = \TooBasic\objectCopyAndEnforce(array('index', 'key', 'primary'), $configs->prefixes, $this->_specs->configs->prefixes);
	}
	protected function parseSpecData($data) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->data)) {
			$this->_specs->data = array();
		}

		global $Connections;

		foreach($data as $datum) {
			$aux = \TooBasic\objectCopyAndEnforce(array('table', 'connection', 'checkfields', 'entries'), $datum, new \stdClass());

			if(!$aux->table || !$aux->checkfields || !$aux->entries) {
				continue;
			}

			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Guessing table identifier and creating a pull for its
			// entries.
			$tKey = sha1("{$aux->connection}-{$aux->table}");
			$this->_specs->data[$tKey] = array();

			foreach($aux->entries as $entry) {
				$auxEntry = new \stdClass();
				$auxEntry->check = $aux->checkfields;
				$auxEntry->entry = $entry;

				$dKey = '';
				foreach($aux->checkfields as $fName) {
					$dKey.= "|{$fName}={$entry->$fName}|";
				}
				$dKey = sha1($dKey);
				$this->_specs->data[$tKey][$dKey] = $auxEntry;
			}
		}
	}
	protected function parseSpecIndexes($indexes) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->indexes)) {
			$this->_specs->indexes = array();
		}
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = array(
			'before_create' => array(),
			'after_create' => array(),
			'before_drop' => array(),
			'after_drop' => array()
		);
		//
		// Checking each index
		foreach($indexes as $index) {
			$aux = \TooBasic\objectCopyAndEnforce(array('name', 'table', 'type', 'connection', 'fields', 'callbacks'), $index, new \stdClass(), array('fields' => array()));

			if(!$aux->fields) {
				continue;
			}
			//
			// Checking specification connection against
			// configuration.
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				// 
				// If there was a connection specified, an error
				// is shown.
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				//
				// Using default instalation connection.
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Obtainig current connection table prefix.
			$prefix = '';
			if(isset($this->_specs->configs->prefixes->{$aux->type})) {
				$prefix = $this->_specs->configs->prefixes->{$aux->type};
			}
			//
			// Generating table's full name.
			$aux->fullname = "{$prefix}{$aux->name}";
			//
			// Generating a key to internally identify current index.
			$key = sha1("{$aux->connection}-{$aux->fullname}");
			//
			// Index callbacks.
			$aux->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $aux->callbacks instanceof \stdClass ? $aux->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
			foreach(array_keys($callbackEntries) as $callbackType) {
				if(!isset($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = array();
				} elseif(!is_array($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = array(
						$aux->callbacks->{$callbackType}
					);
				}

				foreach($aux->callbacks->{$callbackType} as &$call) {
					$callbackKey = "I_{$callbackType}_{$key}";
					if(!isset($this->_callbacks[$callbackKey])) {
						$this->_callbacks[$callbackKey] = array();
					}

					$this->_callbacks[$callbackKey][] = array(
						'name' => $call
					);

					$call = $callbackKey;
				}
			}
			//
			// Accepting index spec.
			$this->_specs->indexes[$key] = $aux;
			if(!isset($this->_perConnection[$aux->connection])) {
				$this->_perConnection[$aux->connection] = array();
			}
			if(!isset($this->_perConnection[$aux->connection]['indexes'])) {
				$this->_perConnection[$aux->connection]['indexes'] = array();
			}
			$this->_perConnection[$aux->connection]['indexes'][] = $aux->fullname;
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
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = array(
			'before_create' => array(),
			'after_create' => array(),
			'before_drop' => array(),
			'after_drop' => array(),
			'before_update' => array(),
			'after_update' => array()
		);

		foreach($tables as $table) {
			//
			// Of there are not fields, this specification is ignored.
			if(!$table->fields) {
				continue;
			}
			//
			// Copying basic fields.
			$aux = \TooBasic\objectCopyAndEnforce(array('name', 'connection', 'prefix', 'engine', 'callbacks'), $table, new \stdClass());
			//
			// Checking specification connection against
			// configuration.
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				// 
				// If there was a connection specified, an error
				// is shown.
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				//
				// Using default instalation connection.
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Obtainig current connection table prefix.
			$prefix = '';
			if(isset($Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX])) {
				$prefix = $Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX];
			}
			//
			// Generating table's full name.
			$aux->fullname = "{$prefix}{$aux->name}";
			//
			// Generating a key to internally identify current table.
			$key = sha1("{$aux->connection}-{$aux->name}");
			//
			// Loading table fields @{
			$aux->fields = array();
			foreach($table->fields as $field) {
				//
				// Copying basic fields.
				$auxField = \TooBasic\objectCopyAndEnforce(array('name', 'type', 'autoincrement', 'null', 'comment', 'callbacks'), $field, new \stdClass(), array('autoincrement' => false, 'null' => false));
				//
				// Generating fullname.
				$auxField->fullname = "{$aux->prefix}{$auxField->name}";
				//
				// If theres no type's type for this field and
				// error is set and it's ignored.
				// Also if the type's type is unknown.
				if(!isset($auxField->type->type)) {
					$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' has no type");
					continue;
				} elseif(!in_array($auxField->type->type, self::$_AllowedColumnTypes)) {
					$this->setError(self::ErrorUnknownType, "Unknown field type '{$auxField->type->type}' for field '{$auxField->fullname}' on table '{$aux->name}'");
					continue;
				}
				//
				//
				/** @todo check this, why there's no 'else'? */
				if(!isset($auxField->type->precision) || !$auxField->type->precision) {
					if($auxField->type->type == self::ColumnTypeEnum && !isset($auxField->type->values)) {
						$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' is enumerative and has no value");
						continue;
					} elseif(!in_array($auxField->type->type, self::$_ColumnTypesWithoutPrecisions)) {
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
				//
				// Field callbacks.
				$auxField->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $auxField->callbacks instanceof \stdClass ? $auxField->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
				foreach(array_keys($callbackEntries) as $callbackType) {
					if(!isset($auxField->callbacks->{$callbackType})) {
						$auxField->callbacks->{$callbackType} = array();
					} elseif(!is_array($auxField->callbacks->{$callbackType})) {
						$auxField->callbacks->{$callbackType} = array(
							$auxField->callbacks->{$callbackType}
						);
					}
					foreach($auxField->callbacks->{$callbackType} as &$call) {
						$callbackKey = "F_{$callbackType}_{$key}_{$auxField->fullname}";
						if(!isset($this->_callbacks[$callbackKey])) {
							$this->_callbacks[$callbackKey] = array();
						}
						$this->_callbacks[$callbackKey][] = array(
							'name' => $call
						);

						$call = $callbackKey;
					}
				}
				//
				// Accepting field specs.
				$aux->fields[$auxField->fullname] = $auxField;
			}
			//
			// If there are no fields for this table it is ignored.
			if(!$aux->fields) {
				continue;
			}
			// @}
			//
			// Table callbacks.
			$aux->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $aux->callbacks instanceof \stdClass ? $aux->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
			foreach(array_keys($callbackEntries) as $callbackType) {
				if(!isset($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = array();
				} elseif(!is_array($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = array(
						$aux->callbacks->{$callbackType}
					);
				}

				foreach($aux->callbacks->{$callbackType} as &$call) {
					$callbackKey = "T_{$callbackType}_{$key}";
					if(!isset($this->_callbacks[$callbackKey])) {
						$this->_callbacks[$callbackKey] = array();
					}
					$this->_callbacks[$callbackKey][] = array(
						'name' => $call
					);

					$call = $callbackKey;
				}
			}
			//
			// Acception table specs.
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
			if(!isset($this->_perConnection[$aux->connection]['tables'])) {
				$this->_perConnection[$aux->connection]['tables'] = array();
			}
			$this->_perConnection[$aux->connection]['tables'][] = $aux->fullname;
		}
	}
	protected function setError($code, $message) {
		$this->_errors[] = array(
			'code' => $code,
			'message' => $message
		);
	}
	protected function updateColumns() {
		foreach($this->_tasks[self::TaskTypeUpdateColumn] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			$adapter = $this->getAdapter($table->connection);
			foreach($columns as $column) {
				$callbackKeyBefore = "F_before_update_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_update_{$tKey}_{$column}";

				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				$adapter->updateTableColumn($table, $column);
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	protected function updateIndexes() {
		foreach($this->_tasks[self::TaskTypeUpdateIndex] as $iKey) {
			$index = $this->_specs->indexes[$iKey];
			$adapter = $this->getAdapter($index->connection);
			$adapter->updateIndex($index);
		}
	}
}
