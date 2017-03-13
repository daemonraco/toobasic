<?php

/**
 * @file DBStructureManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Params;
use TooBasic\Paths;
use TooBasic\SpecsValidator;
use TooBasic\Translate;

/**
 * @class DBStructureManager
 */
class DBStructureManager extends Manager {
	//
	// Constants.
	const ERROR_OK = 0;
	const ERROR_DEFAULT = 1;
	const ERROR_UNKNOWN_TABLE = 2;
	const ERROR_UNKNOWN_TYPE = 3;
	const ERROR_UNKNOWN_CONNECTION = 4;
	const ERROR_UNKNOWN_CALLBACK = 5;
	const COLUMN_TYPE_BLOB = 'blob';
	const COLUMN_TYPE_ENUM = 'enum';
	const COLUMN_TYPE_FLOAT = 'float';
	const COLUMN_TYPE_INT = 'int';
	const COLUMN_TYPE_TEXT = 'text';
	const COLUMN_TYPE_TIMESTAMP = 'timestamp';
	const COLUMN_TYPE_VARCHAR = 'varchar';
	const TASK_TYPE_CREATE_COLUMN = 'create-column';
	const TASK_TYPE_CREATE_INDEX = 'create-indexes';
	const TASK_TYPE_CREATE_TABLE = 'create-tables';
	const TASK_TYPE_DROP_COLUMN = 'drop-column';
	const TASK_TYPE_DROP_INDEX = 'drop-indexes';
	const TASK_TYPE_DROP_TABLE = 'drop-tables';
	const TASK_TYPE_UPDATE_COLUMN = 'update-column';
	const TASK_TYPE_UPDATE_DATA = 'update-data';
	const TASK_TYPE_UPDATE_INDEX = 'update-index';
	const TASK_STATUS = 'status';
	// 
	// Protected properties.
	/**
	 * @var mixed[string] List of SQL files that can be executed in some key
	 * points during an upgrade. They are grouped by type and each leaf is a
	 * simple structure that describes the callback.
	 */
	protected $_callbacks = [];
	/**
	 * @var \TooBasic\Adapters\DB\Adapter[string] List of database connection
	 * adapters already loaded and associated with their names.
	 */
	protected $_dbAdapters = [];
	/**
	 * @var \TooBasic\Managers\DBManager Shortcut to a database connections
	 * manager.
	 */
	protected $_dbManager = false;
	/**
	 * @var \TooBasic\Adapters\DB\DBVersionAdapter[string] List of specs
	 * versions adapters already loaded and associated with their names.
	 */
	protected $_dbVersionAdapters = [];
	/**
	 * @var mixed[] List of errors found while analysing or even upgrading.
	 */
	protected $_errors = [];
	/**
	 * @var boolean This flag is TRUE when the site is flagged as installed.
	 */
	protected $_installed = false;
	/**
	 * @var mixed[string] This property contains lists of all loaded names
	 * associated with their database connection names.
	 */
	protected $_perConnection = [];
	/**
	 * @var string[] List of known specification files.
	 */
	protected $_specFiles = [];
	/**
	 * @var \stdClass Full specifications object.
	 */
	protected $_specs = false;
	/**
	 * @var mixed[] List of operations to be performed during an upgrade.
	 */
	protected $_tasks = false;
	//
	// Public methods.
	/**
	 * This method holds the logic to trigger all necessary checks to
	 * guarantee a healthy and up to date database strucutre. It does not
	 * modifies the database, it just checks it and builds an internal list of
	 * tasks required to solve any found problems.
	 *
	 * @return boolean Returns TRUE when no problem was found in the
	 * structure.
	 */
	public function check() {
		//
		// Default values.
		$ok = false;
		//
		// Checking if there was a loading error. Also, the site must not
		// be flagged as installed.
		if(!$this->_installed && !$this->hasErrors()) {
			//
			// Avoiding multiple checks.
			if($this->_tasks !== false) {
				$ok = $this->_tasks[self::TASK_STATUS];
			} else {
				//
				// Default values.
				$ok = true;
				//
				// List of tasks required to remove structure
				// issues grouped by type.
				$this->_tasks = [
					self::TASK_TYPE_CREATE_INDEX => [],
					self::TASK_TYPE_CREATE_COLUMN => [],
					self::TASK_TYPE_CREATE_TABLE => [],
					self::TASK_TYPE_DROP_COLUMN => [],
					self::TASK_TYPE_DROP_INDEX => [],
					self::TASK_TYPE_DROP_TABLE => [],
					self::TASK_TYPE_UPDATE_COLUMN => [],
					self::TASK_TYPE_UPDATE_DATA => [],
					self::TASK_TYPE_UPDATE_INDEX => []
				];
				//
				// Checking tables existence.
				foreach($this->_specs->tables as $tKey => $table) {
					//
					// Fetching the right database structure
					// adapter.
					$adapter = $this->getAdapter($table->connection);
					//
					// Checking current table existence.
					if(!$adapter->tableExists($table->fullname)) {
						//
						// At this point, the table is
						// added as a required task and
						// the out status is set as FALSE.
						$this->_tasks[self::TASK_TYPE_CREATE_TABLE][] = $tKey;
						$ok = false;
					} else {
						// 
						// Check columns.
						$creates = [];
						$drops = [];
						$updates = [];
						$adapter->compareTable($table, $creates, $drops, $updates);
						//
						// Checking if there's a missing
						// column.
						if($creates) {
							$this->_tasks[self::TASK_TYPE_CREATE_COLUMN][$tKey] = $creates;
							$ok = false;
						}
						//
						// Checking if there's a not
						// specified column. This will be
						// added as task unless the site
						// is flagged to keep unknowns.
						if(!$adapter->keepUnknowns() && $drops) {
							$this->_tasks[self::TASK_TYPE_DROP_COLUMN][$tKey] = $drops;
							$ok = false;
						}
						//
						// Checking if there's a column
						// that does not match with it's
						// definition.
						if($updates) {
							$this->_tasks[self::TASK_TYPE_UPDATE_COLUMN][$tKey] = $updates;
							$ok = false;
						}
					}
				}
				//
				// Checking indexes existence and structure.
				foreach($this->_specs->indexes as $iKey => $index) {
					//
					// Fetching the right database structure
					// adapter.
					$adapter = $this->getAdapter($index->connection);
					//
					// Checking current index existence.
					if(!$adapter->indexExists($index->fullname)) {
						//
						// At this point, the index is
						// added as a required task and
						// the out status is set as FALSE.
						$this->_tasks[self::TASK_TYPE_CREATE_INDEX][] = $iKey;
						$ok = false;
					} else {
						//
						// Checking index definition.
						if(!$adapter->compareIndex($index)) {
							$this->_tasks[self::TASK_TYPE_UPDATE_INDEX][] = $iKey;
							$ok = false;
						}
					}
				}
				//
				// Checking data existence.
				foreach($this->_specs->data as $tKey => $entries) {
					//
					// Fetching the table to be checked.
					$table = $this->_specs->tables[$tKey];
					//
					// Fetching the right database structure
					// adapter.
					$adapter = $this->getAdapter($table->connection);
					//
					// Creating a sub list of tasks.
					$this->_tasks[self::TASK_TYPE_UPDATE_DATA][$tKey] = [];
					//
					// Checking each required entry.
					foreach($entries as $eKey => $entry) {
						if(!$adapter->checkTableEntry($table, $entry)) {
							//
							// At this point, current
							// entry is not present
							// and should be
							// reinserted.
							$this->_tasks[self::TASK_TYPE_UPDATE_DATA][$tKey][] = $eKey;
							$ok = false;
						}
					}
				}
				//
				// Removing empty task's sub-lists to improve
				// further operations.
				foreach($this->_tasks[self::TASK_TYPE_UPDATE_DATA] as $tKey => $eKeys) {
					if(!$eKeys) {
						unset($this->_tasks[self::TASK_TYPE_UPDATE_DATA][$tKey]);
					}
				}
				//
				// Checking if this control has to be more strict
				// or not.
				if(!$this->_dbManager->keepUnknowns()) {
					//
					// Checking on each used connection.
					foreach($this->_perConnection as $connName => $connection) {
						//
						// Fetching the right database
						// structure adapter.
						$adapter = $this->getAdapter($connName);
						//
						// Loading all indexes.
						foreach($adapter->getIndexes() as $dbIndex) {
							//
							// If it's not an
							// specified index, it is
							// added as a task for
							// further removal.
							if(!in_array($dbIndex, $connection[GC_AFIELD_INDEXES])) {
								$this->_tasks[self::TASK_TYPE_DROP_INDEX][] = [
									GC_AFIELD_CONNECTION => $connName,
									GC_AFIELD_NAME => $dbIndex
								];
								$ok = false;
							}
						}
						//
						// Loading all table.
						foreach($adapter->getTables() as $dbTable) {
							//
							// If it's not an
							// specified table, it is
							// added as a task for
							// further removal.
							if(!in_array($dbTable, $connection[GC_AFIELD_TABLES])) {
								$this->_tasks[self::TASK_TYPE_DROP_TABLE][] = [
									GC_AFIELD_CONNECTION => $connName,
									GC_AFIELD_NAME => $dbTable
								];
								$ok = false;
							}
						}
					}
				}
				//
				// Saving current status for further checks.
				$this->_tasks[self::TASK_STATUS] = $ok;
			}
		} else {
			//
			// At this point, only initialization errors has to be
			// considered.
			$ok = !$this->hasErrors();
		}

		return $ok;
	}
	/**
	 * This method provides access to an internal list of errors.
	 *
	 * @return mixed[] Returns a list of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method allows to know if an error was found during intialization
	 * and checks, or even on upgrade operations.
	 *
	 * @return type
	 */
	public function hasErrors() {
		return \boolval($this->_errors);
	}
	/**
	 * This method provides access to the expanded internal specifications
	 * object.
	 *
	 * @return \stdClass Returns a specification object.
	 */
	public function specs() {
		return $this->_specs;
	}
	/**
	 * This method returns the current list of tasks required to keep the
	 * database structure up to date.
	 *
	 * @warning It forced the call to 'check()'.
	 *
	 * @return string[string] Returns a list of required tasks on an upgrade.
	 */
	public function tasks() {
		$this->check();
		return $this->_tasks;
	}
	/**
	 * This method holds the logic to trigger all required tasks to update the
	 * database structure and leave it as it's specified. Also each operation
	 * is executed in the write order.
	 *
	 * @return boolean Returns TRUE if after upgrading, a call to 'check()'
	 * returns TRUE.
	 */
	public function upgrade() {
		//
		// Default values.
		$out = true;
		//
		// Checking if it's actually required an upgrade.
		if(!$this->check()) {
			//
			// Creating non existent tables and columns.
			$this->createTables();
			$this->createColumns();
			//
			// Updating column structures.
			$this->updateColumns();
			//
			// Reinserting required table entries.
			$this->insertData();
			//
			// Creating non existent indexes.
			$this->createIndexes();
			//
			// Updating index structures.
			$this->updateIndexes();
			//
			// Dropping not specified columns, indexes and tables.
			$this->dropColumns();
			$this->dropIndexes();
			$this->dropTables();
			//
			// If it is in emulation mode, it aborts right after
			// upgrading.
			if(isset(Params::Instance()->debugdbemulation)) {
				\TooBasic\debugThing('Database upgrade emulation');
				die;
			}
			//
			// At this point, the list of task is outdated.
			$this->_tasks = false;
			//
			// Rechecking database structure sstatus.
			$out = $this->check();
		}

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method checks the list of loaded callbacks looking for wrong
	 * specifications, unreadable files etc.
	 */
	protected function checkCallbacks() {
		//
		// Checking each callback's file to make sure it exists and can
		// be read, otherwise it is considered an internal error.
		foreach($this->_callbacks as &$subCallbacks) {
			foreach($subCallbacks as &$data) {
				$data[GC_AFIELD_PATH] = Paths::Instance()->dbSpecCallbackPaths($data[GC_AFIELD_NAME]);
				if(!$data[GC_AFIELD_PATH]) {
					$this->setError(self::ERROR_UNKNOWN_CALLBACK, "Unable to find database spec callback '{$data[GC_AFIELD_NAME]}'");
				}
			}
		}
		//
		// Cleaning table callbacks.
		foreach($this->_specs->tables as &$table) {
			$table->realCallbacks = new \stdClass();
			if(isset($table->callbacks)) {
				foreach($table->callbacks as $callbackType => $keys) {
					$table->realCallbacks->{$callbackType} = $keys ? $keys[0] : false;
				}
				unset($table->callbacks);
			}
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
	/**
	 * This method checks loaded specifications and looks for error or
	 * inconsistencies.
	 */
	protected function checkSpecs() {
		//
		// Checking index specifications.
		foreach($this->_specs->indexes as $iKey => &$index) {
			//
			// Guessing the table key associated with the current
			// index.
			$tKey = sha1("{$index->connection}-{$index->table}");
			//
			// Checking if current index belongs to a knwon table, if
			// not, an error is set and it gets remove from specs.
			if(!isset($this->_specs->tables[$tKey])) {
				$this->setError(self::ERROR_UNKNOWN_TABLE, "Index '{$index->fullname}' uses an unknown table called '{$index->table}'");
				unset($this->_specs->indexes[$iKey]);
			} else {
				//
				// Fetching table specs.
				$table = $this->_specs->tables[$tKey];
				//
				// Saving the full table name.
				$index->table = $table->fullname;
				//
				// Loading table prefix and updating fields list.
				$prefix = $table->prefix;
				foreach($index->fields as &$field) {
					$field = "{$prefix}{$field}";
				}
			}
		}
		//
		// Checking data specifications.
		foreach($this->_specs->data as $tKey => &$entries) {
			//
			// Checking if current spec belongs to a knwon table, if
			// not, it gets remove from specs.
			if(!isset($this->_specs->tables[$tKey])) {
				unset($this->_specs->data[$tKey]);
			} else {
				//
				// Fetching table specs.
				$table = $this->_specs->tables[$tKey];
				//
				// Loading table prefix and updating fields list.
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
	/**
	 * This method holds the logic to work on required column tasks and
	 * trigger their creation.
	 */
	protected function createColumns() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_CREATE_COLUMN] as $tKey => $columns) {
			//
			// Fetching table specs.
			$table = $this->_specs->tables[$tKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($table->connection);
			//
			// Checking each required column.
			foreach($columns as $column) {
				//
				// Guessing callback keys.
				$callbackKeyBefore = "F_before_create_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_create_{$tKey}_{$column}";
				//
				// Running callback required before creating the
				// current column.
				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				//
				// Triggering the column creation.
				$adapter->createTableColumn($table, $column);
				//
				// Running callback required before creating the
				// current column.
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required index tasks and trigger
	 * their creation.
	 */
	protected function createIndexes() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_CREATE_INDEX] as $iKey) {
			$index = $this->_specs->indexes[$iKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($index->connection);
			//
			// Guessing callback keys.
			$callbackKeyBefore = "I_before_create_{$iKey}";
			$callbackKeyAfter = "I_after_create_{$iKey}";
			//
			// Running callback required before creating the current
			// index.
			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			//
			// Triggering the index creation.
			$adapter->createIndex($index);
			//
			// Running callback required after creating the current
			// index.
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required table tasks and trigger
	 * their creation.
	 */
	protected function createTables() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_CREATE_TABLE] as $tKey) {
			$table = $this->_specs->tables[$tKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($table->connection);
			//
			// Guessing callback keys.
			$callbackKeyBefore = "T_before_create_{$tKey}";
			$callbackKeyAfter = "T_after_create_{$tKey}";
			//
			// Running callback required before creating the current
			// table.
			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			//
			// Triggering the table creation.
			$adapter->createTable($table);
			//
			// Running callback required after creating the current
			// table.
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required column tasks and
	 * trigger their removal.
	 */
	protected function dropColumns() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_DROP_COLUMN] as $tKey => $columns) {
			$table = $this->_specs->tables[$tKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($table->connection);
			//
			// Checking each column.
			foreach($columns as $column) {
				//
				// Guessing callback keys.
				$callbackKeyBefore = "F_before_drop_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_drop_{$tKey}_{$column}";
				//
				// Running callback required before removing the
				// current column.
				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				//
				// Triggering the column removal.
				$adapter->dropTableColumn($table, $column);
				//
				// Running callback required after removing the
				// current column.
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required idnex tasks and trigger
	 * their removal.
	 */
	protected function dropIndexes() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_DROP_INDEX] as $data) {
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($data[GC_AFIELD_CONNECTION]);
			//
			// Guessing callback keys.
			$callbackKeyBefore = "I_before_drop_{$data[GC_AFIELD_NAME]}";
			$callbackKeyAfter = "I_after_drop_{$data[GC_AFIELD_NAME]}";
			//
			// Running callback required before removing the current
			// index.
			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			//
			// Triggering the index removal.
			$adapter->dropIndex($data[GC_AFIELD_NAME]);
			//
			// Running callback required after removing the current
			// index.
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required table tasks and trigger
	 * their removal.
	 */
	protected function dropTables() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_DROP_TABLE] as $data) {
			//
			// Guessing callback keys.
			$callbackKeyBefore = "T_before_drop_{$data[GC_AFIELD_NAME]}";
			$callbackKeyAfter = "T_after_drop_{$data[GC_AFIELD_NAME]}";
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($data[GC_AFIELD_CONNECTION]);
			//
			// Running callback required before removing the current
			// table.
			if(isset($this->_callbacks[$callbackKeyBefore])) {
				foreach($this->_callbacks[$callbackKeyBefore] as $call) {
					$adapter->executeCallback($call);
				}
			}
			//
			// Triggering the table removal.
			$adapter->dropTable($data[GC_AFIELD_NAME]);
			//
			// Running callback required after removing the current
			// table.
			if(isset($this->_callbacks[$callbackKeyAfter])) {
				foreach($this->_callbacks[$callbackKeyAfter] as $call) {
					$adapter->executeCallback($call);
				}
			}
		}
	}
	/**
	 * This method loads and provides access to a database structure adapter,
	 * and also keeps a shortcut to it further requests.
	 *
	 * @param string $connectionName Name of the connection adapter to
	 * retrive. Also the database connection name.
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database structure
	 * adapter.
	 * @throws \TooBasic\Managers\DBStructureManagerException
	 */
	protected function getAdapter($connectionName) {
		//
		// Default values.
		$out = false;
		//
		// Checking if it's a known adapter (loaded before).
		if(!isset($this->_dbAdapters[$connectionName])) {
			//
			// Global dependencies.
			global $Connections;
			global $Database;
			//
			// Checking connection existence.
			$engine = false;
			if(isset($Connections[GC_CONNECTIONS_DB][$connectionName]) && isset($Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE]) && isset($Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE])) {
				$engine = $Connections[GC_CONNECTIONS_DB][$connectionName][GC_CONNECTIONS_DB_ENGINE];
			} else {
				//
				// If it's an unknown connection it's a fatal
				// configuration error.
				throw new DBStructureManagerException(Translate::Instance()->EX_unable_to_get_connection_conf(['connetion' => $connectionName]));
			}
			//
			// Checking if there's a proper database structure adapter
			// configured.
			if(!isset($Database[GC_DATABASE_DB_SPEC_ADAPTERS][$engine])) {
				throw new DBStructureManagerException(Translate::Instance()->EX_no_adapter_for_engine(['engine' => $engine]));
			}
			//
			// Loading a proper database connection adapter.
			$db = DBManager::Instance()->{$connectionName};
			if($db) {
				//
				// Creating the right adapter.
				$adapterName = $Database[GC_DATABASE_DB_SPEC_ADAPTERS][$engine];
				$this->_dbAdapters[$connectionName] = new $adapterName($db);
				$out = $this->_dbAdapters[$connectionName];
			} else {
				throw new DBStructureManagerException(Translate::Instance()->EX_unable_to_connet_to(['connetion' => $connectionName]));
			}
		} else {
			//
			// Returning a previously loaded adapter.
			$out = $this->_dbAdapters[$connectionName];
		}

		return $out;
	}
	protected function getVersionAdapter($version) {
		//
		// Default values.
		$out = false;
		//
		// Checking if it's a known adapter (loaded before).
		if(!isset($this->_dbVersionAdapters[$version])) {
			//
			// Global dependencies.
			global $Database;
			//
			// Checking version number.
			if(isset($Database[GC_DATABASE_DB_VERSION_ADAPTERS][$version])) {
				$class = $Database[GC_DATABASE_DB_VERSION_ADAPTERS][$version];
				$this->_dbVersionAdapters[$version] = new $class($this);
				$out = $this->_dbVersionAdapters[$version];
			} else {
				throw new DBStructureManagerException(Translate::Instance()->EX_unhandled_version(['version' => $version]));
			}
		} else {
			//
			// Returning a previously loaded adapter.
			$out = $this->_dbVersionAdapters[$version];
		}

		return $out;
	}
	/**
	 * Manager initializer.
	 */
	protected function init() {
		parent::init();
		//
		// Global dependencies.
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
			//
			// Global dependencies.
			global $Connections;
			//
			// No database connections.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB]) || !\boolval($Connections[GC_CONNECTIONS_DB]))) {
				$this->_installed = true;
			}
			//
			// No default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB]) || !$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB])) {
				$this->_installed = true;
			}
			// 
			// Wrong default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB][$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB]]))) {
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
			//
			// Creating a shortcut the database connections manager.
			$this->_dbManager = DBManager::Instance();
			//
			// Loading, parsing and checking specifications.
			$this->loadSpecs();
			$this->parseSpecs();
			$this->checkCallbacks();
			$this->checkSpecs();
			//
			// If required, showing debug information.
			if(isset(Params::Instance()->debugdbstructure)) {
				\TooBasic\debugThing([
					GC_AFIELD_ERRORS => $this->_errors,
					GC_AFIELD_FILES => $this->_specFiles,
					GC_AFIELD_SPECS => $this->_specs,
					GC_AFIELD_CALLBACKS => $this->_callbacks
				]);
			}
		}
	}
	/**
	 * This method holds the logic to work on required data insertion tasks
	 * and trigger their insertion.
	 */
	protected function insertData() {
		//
		// Checking tasks
		foreach($this->_tasks[self::TASK_TYPE_UPDATE_DATA] as $tKey => $eKeys) {
			//
			// Fetching table specs.
			$table = $this->_specs->tables[$tKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($table->connection);
			//
			// Adding each entry.
			foreach($eKeys as $eKey) {
				$entry = $this->_specs->data[$tKey][$eKey];
				$adapter->addTableEntry($table, $entry);
			}
		}
	}
	/**
	 * This method is the one in charge of loading the full list of database
	 * structure specification files.
	 */
	protected function loadSpecs() {
		//
		// Adding configuration.
		$this->_specFiles = Paths::Instance()->dbSpecPaths();
		//
		// Adding default configuration. It allways is the first one.
		global $Database;
		array_unshift($this->_specFiles, $Database[GC_DATABASE_DEFAULT_SPECS]);
		/** @FIXME this should be automatically solve placing the file on 'ROOTDIR/includes/system'. */
	}
	/**
	 * This method takes a specification file, loads its data and triggers all
	 *
	 * @param string $path Absolute specification file path.
	 * @throws \TooBasic\Managers\DBStructureManagerException
	 */
	protected function parseSpec($path) {
		//
		// If not yet created, an object to holds specifications is
		// created.
		if(!$this->_specs) {
			$this->_specs = new \stdClass();
		}
		//
		// Loading file contents.
		$jsonString = file_get_contents($path);
		//
		// Validating JSON strucutre.
		if(!SpecsValidator::ValidateJsonString('db', $jsonString, $info)) {
			throw new DBStructureManagerException(Translate::Instance()->EX_json_path_fail_specs(['path' => $path])." {$info[JV_FIELD_ERROR][JV_FIELD_MESSAGE]}");
		}
		//
		// Checking file contents.
		$json = json_decode($jsonString);
		if(!$json) {
			throw new DBStructureManagerException(Translate::Instance()->EX_JSON_invalid_file([
				'path' => $path,
				'errorcode' => json_last_error(),
				'error' => json_last_error_msg()
			]));
		}
		//
		// Triggering parsings.
		$this->parseSpecConfigs(isset($json->configs) ? $json->configs : new \stdClass());
		$this->parseSpecTables(isset($json->tables) ? $json->tables : []);
		$this->parseSpecIndexes(isset($json->indexes) ? $json->indexes : []);
		$this->parseSpecData(isset($json->data) ? $json->data : []);
	}
	/**
	 * This method parses configurations specifications.
	 *
	 * @param \stdClass $configs Specifications to parse.
	 */
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
		foreach(['prefixes'] as $field) {
			if(!isset($this->_specs->configs->{$field})) {
				$this->_specs->configs->{$field} = new \stdClass();
			}
		}
		//
		// Loading prefixes.
		$this->_specs->configs->prefixes = \TooBasic\objectCopyAndEnforce(['index', 'key', 'primary'], $configs->prefixes, $this->_specs->configs->prefixes);
	}
	/**
	 * This method parses data insertion specifications.
	 *
	 * @param \stdClass $data Specifications to parse.
	 */
	protected function parseSpecData($data) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->data)) {
			$this->_specs->data = [];
		}
		//
		// Global dependencies.
		global $Connections;
		//
		// Analyzing each data spec.
		foreach($data as $datum) {
			//
			// Copying required specification fields into an temporary
			// object.
			$aux = \TooBasic\objectCopyAndEnforce(['table', 'connection', 'checkfields', 'entries'], $datum, new \stdClass());
			//
			// If some of the required fields is not right, this list
			// of entries is ignored.
			if(!$aux->table || !$aux->checkfields || !$aux->entries) {
				continue;
			}
			//
			// Checking connection.
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ERROR_UNKNOWN_CONNECTION, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Guessing table identifier and creating a pull for its
			// entries.
			$tKey = sha1("{$aux->connection}-{$aux->table}");
			if(!isset($this->_specs->data[$tKey])) {
				$this->_specs->data[$tKey] = [];
			}
			//
			// Checking and expanding each entry.
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
	/**
	 * This method parses index specifications.
	 *
	 * @param \stdClass $indexes Specifications to parse.
	 */
	protected function parseSpecIndexes($indexes) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->indexes)) {
			$this->_specs->indexes = [];
		}
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = [
			GC_AFIELD_BEFORE_CREATE => [],
			GC_AFIELD_AFTER_CREATE => [],
			GC_AFIELD_BEFORE_DROP => [],
			GC_AFIELD_AFTER_DROP => []
		];
		//
		// Checking each index.
		foreach($indexes as $index) {
			$aux = \TooBasic\objectCopyAndEnforce(['name', 'table', 'type', 'connection', 'fields', 'callbacks'], $index, new \stdClass(), ['fields' => []]);

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
					$this->setError(self::ERROR_UNKNOWN_CONNECTION, "Unknown connection named '{$aux->connection}'");
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
			//
			// Parsing and expanding callbacks list.
			foreach(array_keys($callbackEntries) as $callbackType) {
				if(!isset($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = [];
				} elseif(!is_array($aux->callbacks->{$callbackType})) {
					$aux->callbacks->{$callbackType} = [
						$aux->callbacks->{$callbackType}
					];
				}

				foreach($aux->callbacks->{$callbackType} as &$call) {
					$callbackKey = "I_{$callbackType}_{$key}";
					if(!isset($this->_callbacks[$callbackKey])) {
						$this->_callbacks[$callbackKey] = [];
					}

					$this->_callbacks[$callbackKey][] = [
						GC_AFIELD_NAME => $call
					];

					$call = $callbackKey;
				}
			}
			//
			// Accepting index spec.
			$this->_specs->indexes[$key] = $aux;
			//
			// Enforcing structure @{
			if(!isset($this->_perConnection[$aux->connection])) {
				$this->_perConnection[$aux->connection] = [];
			}
			if(!isset($this->_perConnection[$aux->connection][GC_AFIELD_INDEXES])) {
				$this->_perConnection[$aux->connection][GC_AFIELD_INDEXES] = [];
			}
			// @}
			$this->_perConnection[$aux->connection][GC_AFIELD_INDEXES][] = $aux->fullname;
		}
	}
	/**
	 * This method triggers the analysis of all specification files and some
	 * basic checks on their contents.
	 */
	protected function parseSpecs() {
		//
		// Parsing each specification file.
		foreach($this->_specFiles as $path) {
			$this->parseSpec($path);
		}
		//
		// Enforcing and cleaning full lists of index and table names.
		foreach($this->_perConnection as &$connection) {
			//
			// Enforcing structure @{
			if(!isset($connection[GC_AFIELD_TABLES])) {
				$connection[GC_AFIELD_TABLES] = [];
			}
			if(!isset($connection[GC_AFIELD_INDEXES])) {
				$connection[GC_AFIELD_INDEXES] = [];
			}
			// @}

			foreach($connection as &$type) {
				$type = array_unique($type);
			}
		}
	}
	/**
	 * This method parses table specifications.
	 *
	 * @param \stdClass $tables Specifications to parse.
	 */
	protected function parseSpecTables($tables) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->tables)) {
			$this->_specs->tables = [];
		}
		//
		// Checking each table.
		foreach($tables as $table) {
			//
			// Default values.
			$version = 'v1';
			//
			// Guessing version.
			if(isset($table->version)) {
				$version = "v{$table->version}";
			}
			//
			// Loading version adapter.
			$versionAdapter = $this->getVersionAdapter($version);
			//
			// Parsing table with the proper versionadapter.
			$results = $versionAdapter->parseTable($table, $this->_callbacks);
			//
			// Importing found errors.
			foreach($results[GC_AFIELD_ERRORS] as $error) {
				$this->setError($error[GC_AFIELD_CODE], $error[GC_AFIELD_MESSAGE]);
			}
			//
			// Checking if the current table should be ignored.
			if($results[GC_AFIELD_IGNORED]) {
				continue;
			} else {
				//
				// Accepting table specs.
				if(!isset($this->_specs->tables[$results[GC_AFIELD_KEY]])) {
					$this->_specs->tables[$results[GC_AFIELD_KEY]] = $results[GC_AFIELD_SPECS];
				} else {
					if($this->_specs->tables[$results[GC_AFIELD_KEY]]->prefix == $results[GC_AFIELD_SPECS]->prefix) {
						foreach($results[GC_AFIELD_SPECS]->fields as $field) {
							$this->_specs->tables[$results[GC_AFIELD_KEY]]->fields[$field->fullname] = $field;
						}
					}
				}
				//
				// Enforcing structure @{
				if(!isset($this->_perConnection[$results[GC_AFIELD_SPECS]->connection])) {
					$this->_perConnection[$results[GC_AFIELD_SPECS]->connection] = [];
				}
				if(!isset($this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES])) {
					$this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES] = [];
				}
				// @}
				$this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES][] = $results[GC_AFIELD_SPECS]->fullname;
				//
				// Updating callbacks.
				$this->_callbacks = $results[GC_AFIELD_CALLBACKS];
				//
				// Checking if there are index specifications to
				// consider.
				if($results[GC_AFIELD_INDEXES]) {
					/** @fixme this is a duplicated code, it should go into a method */
					foreach($results[GC_AFIELD_INDEXES] as &$auxIndex) {
						//
						// Obtainig current connection table prefix.
						$prefix = '';
						if(isset($this->_specs->configs->prefixes->{$auxIndex->type})) {
							$prefix = $this->_specs->configs->prefixes->{$auxIndex->type};
						}
						//
						// Generating table's full name.
						$auxIndex->fullname = "{$prefix}{$auxIndex->name}";
						//
						// Generating a key to internally identify current index.
						$key = sha1("{$auxIndex->connection}-{$auxIndex->fullname}");
						//
						// Accepting index spec.
						$this->_specs->indexes[$key] = $auxIndex;
						//
						// Enforcing structure @{
						if(!isset($this->_perConnection[$auxIndex->connection])) {
							$this->_perConnection[$auxIndex->connection] = [];
						}
						if(!isset($this->_perConnection[$auxIndex->connection][GC_AFIELD_INDEXES])) {
							$this->_perConnection[$auxIndex->connection][GC_AFIELD_INDEXES] = [];
						}
						// @}
						$this->_perConnection[$auxIndex->connection][GC_AFIELD_INDEXES][] = $auxIndex->fullname;
					}
				}
			}
		}
	}
	/**
	 * This method adds an error to an internal list for furter analysis.
	 *
	 * @param int $code Error identifier.
	 * @param message $message Error's explanation.
	 */
	protected function setError($code, $message) {
		$this->_errors[] = [
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message
		];
	}
	/**
	 * This method holds the logic to work on required column tasks and
	 * trigger their structure update.
	 */
	protected function updateColumns() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_UPDATE_COLUMN] as $tKey => $columns) {
			//
			// Fetching table specs.
			$table = $this->_specs->tables[$tKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($table->connection);
			//
			// Checking each required column.
			foreach($columns as $column) {
				//
				// Guessing callback keys.
				$callbackKeyBefore = "F_before_update_{$tKey}_{$column}";
				$callbackKeyAfter = "F_after_update_{$tKey}_{$column}";
				//
				// Running callback required before creating the
				// current column.
				if(isset($this->_callbacks[$callbackKeyBefore])) {
					foreach($this->_callbacks[$callbackKeyBefore] as $call) {
						$adapter->executeCallback($call);
					}
				}
				//
				// Triggering the column updte.
				$adapter->updateTableColumn($table, $column);
				//
				// Running callback required before creating the
				// current column.
				if(isset($this->_callbacks[$callbackKeyAfter])) {
					foreach($this->_callbacks[$callbackKeyAfter] as $call) {
						$adapter->executeCallback($call);
					}
				}
			}
		}
	}
	/**
	 * This method holds the logic to work on required index tasks and trigger
	 * their structure update.
	 */
	protected function updateIndexes() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TASK_TYPE_UPDATE_INDEX] as $iKey) {
			//
			// Fetching index specs.
			$index = $this->_specs->indexes[$iKey];
			//
			// Fetching the right database structure adapter.
			$adapter = $this->getAdapter($index->connection);
			//
			// Triggering the index update.
			$adapter->updateIndex($index);
		}
	}
}
