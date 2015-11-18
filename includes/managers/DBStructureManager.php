<?php

/**
 * @file DBStructureManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use \TooBasic\Params;
use \TooBasic\Paths;

/**
 * @class DBStructureManagerExeption
 * This is a specific exception class for fatal errors found on a database
 * structure check and update.
 */
class DBStructureManagerExeption extends \TooBasic\DBException {
	
}

/**
 * @class DBStructureManager
 */
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
	// Protected properties.
	/**
	 * @var mixed[string] List of SQL files that can be executed in some key
	 * points during an upgrade. They are grouped by type and each leaf is a
	 * simple structure that describes the callback.
	 */
	protected $_callbacks = array();
	/**
	 * @var \TooBasic\Adapters\DB\Adapter[string] List of database connection
	 * adapters already loaded and associated with their names.
	 */
	protected $_dbAdapters = array();
	/**
	 * @var \TooBasic\Managers\DBManager Shortcut to a database connections
	 * manager.
	 */
	protected $_dbManager = false;
	/**
	 * @var \TooBasic\Adapters\DB\DBVersionAdapter[string] List of specs
	 * versions adapters already loaded and associated with their names.
	 */
	protected $_dbVersionAdapters = array();
	/**
	 * @var mixed[] List of errors found while analysing or even upgrading.
	 */
	protected $_errors = array();
	/**
	 * @var boolean This flag is TRUE when the site is flagged as installed.
	 */
	protected $_installed = false;
	/**
	 * @var mixed[string] This property contains lists of all loaded names
	 * associated with their database connection names.
	 */
	protected $_perConnection = array();
	/**
	 * @var string[] List of known specification files.
	 */
	protected $_specFiles = array();
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
				$ok = $this->_tasks[self::TaskStatus];
			} else {
				//
				// Default values.
				$ok = true;
				//
				// List of tasks required to remove structure
				// issues grouped by type.
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
						$this->_tasks[self::TaskTypeCreateTable][] = $tKey;
						$ok = false;
					} else {
						// 
						// Check columns.
						$creates = array();
						$drops = array();
						$updates = array();
						$adapter->compareTable($table, $creates, $drops, $updates);
						//
						// Checking if there's a missing
						// column.
						if($creates) {
							$this->_tasks[self::TaskTypeCreateColumn][$tKey] = $creates;
							$ok = false;
						}
						//
						// Checking if there's a not
						// specified column. This will be
						// added as task unless the site
						// is flagged to keep unknowns.
						if(!$adapter->keepUnknowns() && $drops) {
							$this->_tasks[self::TaskTypeDropColumn][$tKey] = $drops;
							$ok = false;
						}
						//
						// Checking if there's a column
						// that does not match with it's
						// definition.
						if($updates) {
							$this->_tasks[self::TaskTypeUpdateColumn][$tKey] = $updates;
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
						$this->_tasks[self::TaskTypeCreateIndex][] = $iKey;
						$ok = false;
					} else {
						//
						// Checking index definition.
						if(!$adapter->compareIndex($index)) {
							$this->_tasks[self::TaskTypeUpdateIndex][] = $iKey;
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
					$this->_tasks[self::TaskTypeUpdateData][$tKey] = array();
					//
					// Checking each required entry.
					foreach($entries as $eKey => $entry) {
						if(!$adapter->checkTableEntry($table, $entry)) {
							//
							// At this point, current
							// entry is not present
							// and should be
							// reinserted.
							$this->_tasks[self::TaskTypeUpdateData][$tKey][] = $eKey;
							$ok = false;
						}
					}
				}
				//
				// Removing empty task's sub-lists to improve
				// further operations.
				foreach($this->_tasks[self::TaskTypeUpdateData] as $tKey => $eKeys) {
					if(!$eKeys) {
						unset($this->_tasks[self::TaskTypeUpdateData][$tKey]);
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
								$this->_tasks[self::TaskTypeDropIndex][] = array(
									GC_AFIELD_CONNECTION => $connName,
									GC_AFIELD_NAME => $dbIndex
								);
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
								$this->_tasks[self::TaskTypeDropTable][] = array(
									GC_AFIELD_CONNECTION => $connName,
									GC_AFIELD_NAME => $dbTable
								);
								$ok = false;
							}
						}
					}
				}
				//
				// Saving current status for further checks.
				$this->_tasks[self::TaskStatus] = $ok;
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
					$this->setError(self::ErrorUnknownCallback, "Unable to find database spec callback '{$data[GC_AFIELD_NAME]}'");
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
				$this->setError(self::ErrorUnknownTable, "Index '{$index->fullname}' uses an unknown table called '{$index->table}'");
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
		foreach($this->_tasks[self::TaskTypeCreateColumn] as $tKey => $columns) {
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
		foreach($this->_tasks[self::TaskTypeCreateIndex] as $iKey) {
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
		foreach($this->_tasks[self::TaskTypeCreateTable] as $tKey) {
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
		foreach($this->_tasks[self::TaskTypeDropColumn] as $tKey => $columns) {
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
		foreach($this->_tasks[self::TaskTypeDropIndex] as $data) {
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
		foreach($this->_tasks[self::TaskTypeDropTable] as $data) {
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
	 * @throws \TooBasic\Managers\DBStructureManagerExeption
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
				throw new DBStructureManagerExeption("Unable to obtain connection '{$connectionName}' configuration");
			}
			//
			// Checking if there's a proper database structure adapter
			// configured.
			if(!isset($Database[GC_DATABASE_DB_SPEC_ADAPTERS][$engine])) {
				throw new DBStructureManagerExeption("There's no adapter for engine '{$engine}'");
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
				throw new DBStructureManagerExeption("Unable to obtaing a connetion to '{$connectionName}'");
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
				throw new DBStructureManagerExeption("Unable to handle version '{$version}'");
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
				\TooBasic\debugThing(array(
					GC_AFIELD_ERRORS => $this->_errors,
					GC_AFIELD_FILES => $this->_specFiles,
					GC_AFIELD_SPECS => $this->_specs,
					GC_AFIELD_CALLBACKS => $this->_callbacks
				));
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
		foreach($this->_tasks[self::TaskTypeUpdateData] as $tKey => $eKeys) {
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
		/** @fixme this should be automatically solve placing the file on 'ROOTDIR/includes/system'. */
	}
	/**
	 * This method takes a specification file, loads its data and triggers all
	 *
	 * @param string $path Absolute specification file path.
	 * @throws \TooBasic\Managers\DBStructureManagerExeption
	 */
	protected function parseSpec($path) {
		//
		// If not yet created, an object to holds specifications is
		// created.
		if(!$this->_specs) {
			$this->_specs = new \stdClass();
		}
		//
		// Loading and checking file.
		$json = json_decode(file_get_contents($path));
		if(!$json) {
			throw new DBStructureManagerExeption("JSON spec at '{$path}' is broken. [".json_last_error().'] '.json_last_error_msg());
		}
		//
		// Triggering parsings.
		$this->parseSpecConfigs(isset($json->configs) ? $json->configs : new \stdClass());
		$this->parseSpecTables(isset($json->tables) ? $json->tables : array());
		$this->parseSpecIndexes(isset($json->indexes) ? $json->indexes : array());
		$this->parseSpecData(isset($json->data) ? $json->data : array());
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
		foreach(array('prefixes') as $field) {
			if(!isset($this->_specs->configs->{$field})) {
				$this->_specs->configs->{$field} = new \stdClass();
			}
		}
		//
		// Loading prefixes.
		$this->_specs->configs->prefixes = \TooBasic\objectCopyAndEnforce(array('index', 'key', 'primary'), $configs->prefixes, $this->_specs->configs->prefixes);
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
			$this->_specs->data = array();
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
			$aux = \TooBasic\objectCopyAndEnforce(array('table', 'connection', 'checkfields', 'entries'), $datum, new \stdClass());
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
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Guessing table identifier and creating a pull for its
			// entries.
			$tKey = sha1("{$aux->connection}-{$aux->table}");
			if(!isset($this->_specs->data[$tKey])) {
				$this->_specs->data[$tKey] = array();
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
			$this->_specs->indexes = array();
		}
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = array(
			GC_AFIELD_BEFORE_CREATE => array(),
			GC_AFIELD_AFTER_CREATE => array(),
			GC_AFIELD_BEFORE_DROP => array(),
			GC_AFIELD_AFTER_DROP => array()
		);
		//
		// Checking each index.
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
			//
			// Parsing and expanding callbacks list.
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
						GC_AFIELD_NAME => $call
					);

					$call = $callbackKey;
				}
			}
			//
			// Accepting index spec.
			$this->_specs->indexes[$key] = $aux;
			//
			// Enforcing structure @{
			if(!isset($this->_perConnection[$aux->connection])) {
				$this->_perConnection[$aux->connection] = array();
			}
			if(!isset($this->_perConnection[$aux->connection][GC_AFIELD_INDEXES])) {
				$this->_perConnection[$aux->connection][GC_AFIELD_INDEXES] = array();
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
				$connection[GC_AFIELD_TABLES] = array();
			}
			if(!isset($connection[GC_AFIELD_INDEXES])) {
				$connection[GC_AFIELD_INDEXES] = array();
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
			$this->_specs->tables = array();
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
					$this->_perConnection[$results[GC_AFIELD_SPECS]->connection] = array();
				}
				if(!isset($this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES])) {
					$this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES] = array();
				}
				// @}
				$this->_perConnection[$results[GC_AFIELD_SPECS]->connection][GC_AFIELD_TABLES][] = $results[GC_AFIELD_SPECS]->fullname;
				//
				// Updating callbacks.
				$this->_callbacks = $results[GC_AFIELD_CALLBACKS];
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
		$this->_errors[] = array(
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message
		);
	}
	/**
	 * This method holds the logic to work on required column tasks and
	 * trigger their structure update.
	 */
	protected function updateColumns() {
		//
		// Checking tasks.
		foreach($this->_tasks[self::TaskTypeUpdateColumn] as $tKey => $columns) {
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
		foreach($this->_tasks[self::TaskTypeUpdateIndex] as $iKey) {
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
