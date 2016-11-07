<?php

/**
 * @file Adapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

//
// Class aliases.
use TooBasic\DBException;
use TooBasic\Params;
use TooBasic\Translate;

/**
 * @class Adapter
 * This class represents a basic database connection adapter and provides access
 * basic operations like querying, preparing statements, etc.
 */
class Adapter extends \TooBasic\Adapters\Adapter {
	//
	// Protected properties.
	/**
	 * @var string Name of the database engine being used.
	 */
	protected $_engine = false;
	/**
	 * @var \PDO Database connection shortcut.
	 */
	protected $_dblink = false;
	/**
	 * @var string Tables prefix shortcut.
	 */
	protected $_prefix = '';
	//
	// Magic methods.
	public function __construct($dbname) {
		parent::__construct();
		//
		// Loading global connections settings.
		global $Connections;
		//
		// Checking requested connection.
		self::SanitizeConnectionSpec($dbname);
		if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
			$connString = $this->getConnectionString($dbname);
			if(isset(Params::Instance()->debugdb)) {
				$out = "DB connection: {$dbname} [{$this->engine()}]\n\n";
				$out.= "DB connection data:\n";
				$out.= "        Connection string: '{$connString}'\n";
				foreach($Connections[GC_CONNECTIONS_DB][$dbname] as $key => $value) {
					if($key != GC_CONNECTIONS_DB_PASSWORD && $value) {
						$out.= "        {$key}: '{$value}'\n";
					}
				}
				\TooBasic\debugThing($out);
			}
			//
			// Attempting to connect.
			try {
				//
				// Creating the connection object.
				$this->_dblink = new \PDO($connString, $Connections[GC_CONNECTIONS_DB][$dbname][GC_CONNECTIONS_DB_USERNAME], $Connections[GC_CONNECTIONS_DB][$dbname][GC_CONNECTIONS_DB_PASSWORD]);
			} catch(\PDOException $e) {
				//
				// If there is a database connection exception, it is
				// caught and a user exception is raised.
				throw new DBException(Translate::Instance()->EX_PDO_unable_to_connect([
					'code' => $e->getCode(),
					'message' => $e->getMessage()
				]), $e->getCode(), $e);
			}
			//
			// Creating a shortcut for database tables prefix
			$this->_prefix = $Connections[GC_CONNECTIONS_DB][$dbname][GC_CONNECTIONS_DB_PREFIX];
		}
	}
	//
	// Public methods.
	/**
	 * Allows to know if this adapter is currently connected to a database.
	 *
	 * @return boolean Returns TRUE when the database link is connected.
	 */
	public function connected() {
		return boolval($this->_dblink);
	}
	/**
	 * This method allows to know what database engine is being adapted.
	 * @return string Returns a database type name.
	 */
	public function engine() {
		return $this->_engine;
	}
	/**
	 * This method povides access to the last error caught by the database
	 * link.
	 *
	 * @return int Returns a database error code.
	 */
	public function errorCode() {
		return $this->_dblink ? $this->_dblink->errorCode() : false;
	}
	/**
	 * This method povides access to the last error caught by the database
	 * link.
	 *
	 * @return string[] Returns a database error information.
	 */
	public function errorInfo() {
		return $this->_dblink ? $this->_dblink->errorInfo() : false;
	}
	/**
	 * This method is similar than 'query()', but it uses the method
	 * '\PDO::exec()'.
	 *
	 * @param string $query SQL query to be executed.
	 * @param boolean $dieOnError When true, if there were any problem
	 * performing the query, it raises an exception.
	 * @return int Returns the number of rows that were modified or deleted by
	 * the SQL statement you issued. If no rows were affected, returns 0
	 */
	public function exec($query, $dieOnError = true) {
		//
		// Setting the default value to be returned.
		$result = false;
		//
		// Checking connection.
		if($this->connected()) {
			try {
				//
				// Attempting to execute the requested query.
				$result = $this->_dblink->exec($query);
			} catch(\PDOException $e) {
				//
				// If there were any issue with the query and the result
				// is set to false.
				$result = false;
			}
		}
		//
		// Checking that there where no errors, and if there were and it's
		// specified, and exception is raised.
		if($dieOnError && $result === false) {
			if($this->connected()) {
				$info = $this->_dblink->errorInfo();
				throw new DBException(Translate::Instance()->EX_PDO_unable_to_run_query([
					'query' => $query,
					'engine' => $this->_engine,
					'code' => $this->_dblink->errorCode(),
					'info0' => $info[0],
					'info1' => $info[1],
					'info2' => $info[2]
				]));
			} else {
				throw new DBException(Translate::Instance()->EX_not_connected);
			}
		}
		//
		// Returning the resulting statement.
		return $result;
	}
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS];
	}
	/**
	 * This method allows to get the last id used by an INSERT operation.
	 *
	 * @param string $name Name of the sequence object from which the ID
	 * should be returned.
	 * @return int Returns the last inserted id.
	 */
	public function lastInsertId($name = null) {
		return $this->_dblink->lastInsertId($name);
	}
	/**
	 * This method allows to get the database connection object.
	 *
	 * @return \PDO Returns a connection pointer.
	 */
	public function & link() {
		return $this->_dblink;
	}
	/**
	 * This method allows to prepare a statement based on a query.
	 *
	 * @param string $query SQL query to use for the statement creation.
	 * @return \PDOStatement Returns a pointer to the new statement.
	 */
	public function & prepare($query, $dieOnError = true) {
		//
		// Preparing the new statement.
		$out = $this->_dblink->prepare($query);
		//
		// Setting the statement to return results as associative arrays.
		if($out) {
			$out->setFetchMode(\PDO::FETCH_ASSOC);
		} elseif($dieOnError) {
			$info = $this->_dblink->errorInfo();
			throw new DBException(Translate::Instance()->EX_PDO_unable_to_prepate_query([
				'query' => $query,
				'engine' => $this->_engine,
				'code' => $this->_dblink->errorCode(),
				'info0' => $info[0],
				'info1' => $info[1],
				'info2' => $info[2]
			]));
		}
		//
		// Returning requested statement.
		return $out;
	}
	/**
	 * Returns current database configurated prefix.
	 * 
	 * @return string Return a database tables prefix.
	 */
	public function prefix() {
		return $this->_prefix;
	}
	/**
	 * This method allows to directly execute a query on the database.
	 *
	 * @param string $query SQL query to be executed.
	 * @param boolean $dieOnError When true, if there were any problem
	 * performing the query, it raises an exception.
	 * @return \PDOStatement Returning the statement generated by the query. On
	 * error, returns false.
	 */
	public function query($query, $dieOnError = true) {
		//
		// Setting the default value to be returned.
		$result = false;
		//
		// Checking connection.
		if($this->connected()) {
			try {
				//
				// Attempting to execute the requested query.
				$result = $this->_dblink->query($query, \PDO::FETCH_ASSOC);
			} catch(\PDOException $e) {
				//
				// If there were any issue with the query and the result
				// is set to false.
				$result = false;
			}
		}
		//
		// Checking that there where no errors, and if there were and it's
		// specified, and exception is raised.
		if($dieOnError && $result === false) {
			if($this->connected()) {
				$info = $this->_dblink->errorInfo();
				throw new DBException(Translate::Instance()->EX_PDO_unable_to_run_query([
					'query' => $query,
					'engine' => $this->_engine,
					'code' => $this->_dblink->errorCode(),
					'info0' => $info[0],
					'info1' => $info[1],
					'info2' => $info[2]
				]));
			} else {
				throw new DBException(Translate::Instance()->EX_not_connected);
			}
		}
		//
		// Returning the resulting statement.
		return $result;
	}
	/**
	 * This method provides access to the proper query adapter for this
	 * adapter's engine.
	 *
	 * @return \TooBasic\Adapters\DB\QueryAdapter Returns a query adapeter
	 * pointer.
	 * @throws \TooBasic\DBException
	 */
	public function queryAdapter() {
		$out = false;
		//
		// Global dependencies.
		global $Database;
		//
		// Checking that there's an adapter for current engine.
		if(isset($Database[GC_DATABASE_DB_QUERY_ADAPTERS][$this->engine()])) {
			//
			// Obtaining the right adapter.
			$out = \TooBasic\Adapters\Adapter::Factory($Database[GC_DATABASE_DB_QUERY_ADAPTERS][$this->engine()]);
		} else {
			throw new DBException(Translate::Instance()->EX_undefined_adapter_for_engine(['engine' => $this->engine()]));
		}

		return $out;
	}
	/**
	 * Similar to query() but it always returns an array of rows.
	 *
	 * @param string $query SQL query to be executed.
	 * @param boolean $dieOnError When true, if there were any problem
	 * performing the query, it raises an exception.
	 * @return mixed[] Returns a list of found items.
	 */
	public function queryData($query, $dieOnError = true) {
		$out = [];

		$result = $this->query($query, $dieOnError);
		if($result) {
			$out = $result->fetchAll();
		}

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method builds a complete connection string to be use on PDO
	 * connections (a.k.a. DSN).
	 *
	 * @param string $dbname Name of the database configuration to use.
	 * @return string Returns a DSN useful for PDO connections.
	 */
	protected function getConnectionString($dbname) {
		//
		// Default values.
		$out = '';
		//
		// Global dependencies.
		global $Connections;
		//
		// Checking configuration existence.
		if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
			//
			// Configuration shortcut.
			$connData = &$Connections[GC_CONNECTIONS_DB][$dbname];
			//
			// Storing the engine's name.
			$this->_engine = $connData[GC_CONNECTIONS_DB_ENGINE];
			//
			// Build a proper DSN based on its database type/engine.
			switch($this->engine()) {
				case 'sqlite':
					$connData[GC_CONNECTIONS_DB_USERNAME] = false;
					$connData[GC_CONNECTIONS_DB_PASSWORD] = false;

					$out = $connData[GC_CONNECTIONS_DB_ENGINE];
					$out.= ":{$connData[GC_CONNECTIONS_DB_SERVER]}";
					break;
				case 'mysql':
				case 'pgsql':
				default:
					$out = $connData[GC_CONNECTIONS_DB_ENGINE];
					$out.= ":host={$connData[GC_CONNECTIONS_DB_SERVER]}";
					if($connData[GC_CONNECTIONS_DB_PORT]) {
						$out.= ";port={$connData[GC_CONNECTIONS_DB_PORT]}";
					}
					$out.= ";dbname={$connData[GC_CONNECTIONS_DB_NAME]}";
					//$out.= ";user={$connData[GC_CONNECTIONS_DB_USERNAME]}";
					//$out.= ";password={$connData[GC_CONNECTIONS_DB_PASSWORD]}";
					break;
			}
		} else {
			$this->_engine = false;
		}

		return $out;
	}
	//
	// Public class methods.
	/**
	 * This class method ensures the right structure of a database
	 * conifiguraion entry.
	 *
	 * @param string $dbname Name of the database configuration to check.
	 */
	public static function SanitizeConnectionSpec($dbname) {
		//
		// Global dependencies.
		global $Connections;
		//
		// Checking configuration existence.
		if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
			//
			// Configuration shortcut.
			$connData = &$Connections[GC_CONNECTIONS_DB][$dbname];
			//
			// Checking/enforcing port configuration.
			if(!isset($connData[GC_CONNECTIONS_DB_PORT]) || !$connData[GC_CONNECTIONS_DB_PORT]) {
				$connData[GC_CONNECTIONS_DB_PORT] = false;
			}
			//
			// Checking/enforcing table prefixes configuration.
			if(!isset($connData[GC_CONNECTIONS_DB_PREFIX]) || !$connData[GC_CONNECTIONS_DB_PREFIX]) {
				$connData[GC_CONNECTIONS_DB_PREFIX] = '';
			}
			//
			// Checking/enforcing SID configuration.
			if(!isset($connData[GC_CONNECTIONS_DB_SID]) || !$connData[GC_CONNECTIONS_DB_SID]) {
				$connData[GC_CONNECTIONS_DB_SID] = false;
			}
			//
			// Checking/enforcing passwords configuration.
			if(!isset($connData[GC_CONNECTIONS_DB_PASSWORD]) || !$connData[GC_CONNECTIONS_DB_PASSWORD]) {
				$connData[GC_CONNECTIONS_DB_PASSWORD] = false;
			}
			//
			// Checking basic connection information.
			// When some of these configurations is not present, the
			// entire entry is dropped.
			if(!isset($connData[GC_CONNECTIONS_DB_ENGINE]) || !isset($connData[GC_CONNECTIONS_DB_SERVER]) || !isset($connData[GC_CONNECTIONS_DB_NAME]) || !isset($connData[GC_CONNECTIONS_DB_USERNAME])) {
				unset($connData);
			}
		}
	}
}
