<?php

namespace TooBasic;

class DBAdapter extends Adapter {
	//
	// Constants.
	//
	// Protected properties.
	protected $_engine = false;
	protected $_dblink = false;
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
				throw new \TooBasic\DBException("Unable to connect to database. [PDO-{$e->getCode()}] {$e->getMessage()}", $e->getCode(), $e);
			}
			//
			// Creating a shortcut for database tables prefix
			$this->_prefix = $Connections[GC_CONNECTIONS_DB][$dbname][GC_CONNECTIONS_DB_PREFIX];
		}
	}
	//
	// Public methods.
	public function connected() {
		return boolval($this->_dblink);
	}
	public function engine() {
		return $this->_engine;
	}
	public function errorCode() {
		return $this->_dblink ? $this->_dblink->errorCode() : false;
	}
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
				throw new \TooBasic\DBException("Unable to run query: {$query}. {$this->_engine} Error: [{$this->_dblink->errorCode()}] {$info[0]}-{$info[1]}-{$info[2]}");
			} else {
				throw new \TooBasic\DBException('Not connected');
			}
		}
		//
		// Returning the resulting statement.
		return $result;
	}
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_KEEPUNKNOWNS];
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
	 * @todo doc
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
			throw new \TooBasic\DBException("Unable to prepate query: {$query}. {$this->_engine} Error: [{$this->_dblink->errorCode()}] {$info[0]}-{$info[1]}-{$info[2]}");
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
				throw new \TooBasic\DBException("Unable to run query: {$query}. {$this->_engine} Error: [{$this->_dblink->errorCode()}] {$info[0]}-{$info[1]}-{$info[2]}");
			} else {
				throw new \TooBasic\DBException('Not connected');
			}
		}
		//
		// Returning the resulting statement.
		return $result;
	}
	/**
	 * @todo doc
	 *
	 * @return \TooBasic\DBQueryAdapter @todo doc
	 * @throws \TooBasic\DBException @todo doc
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
			$out = \TooBasic\Adapter::Factory($Database[GC_DATABASE_DB_QUERY_ADAPTERS][$this->engine()]);
		} else {
			throw new \TooBasic\DBException("There's no define adapter for a '{$this->engine()}' connection");
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
		$out = array();

		$result = $this->query($query, $dieOnError);
		if($result) {
			$out = $result->fetchAll();
		}

		return $out;
	}
	//
	// Protected methods.
	protected function getConnectionString($dbname) {
		$out = '';

		global $Connections;

		if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
			$connData = &$Connections[GC_CONNECTIONS_DB][$dbname];

			$this->_engine = $connData[GC_CONNECTIONS_DB_ENGINE];

			switch($this->engine()) {
				case 'sqlite':
					$connData[GC_CONNECTIONS_DB_USERNAME] = false;
					$connData[GC_CONNECTIONS_DB_PASSWORD] = false;

					$out = $connData[GC_CONNECTIONS_DB_ENGINE];
					$out.= ":{$connData[GC_CONNECTIONS_DB_SERVER]}";
					break;
				case 'mysql':
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
	public static function SanitizeConnectionSpec($dbname) {
		global $Connections;

		if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
			$connData = &$Connections[GC_CONNECTIONS_DB][$dbname];

			if(!isset($connData[GC_CONNECTIONS_DB_PORT]) || !$connData[GC_CONNECTIONS_DB_PORT]) {
				$connData[GC_CONNECTIONS_DB_PORT] = false;
			}
			if(!isset($connData[GC_CONNECTIONS_DB_PREFIX]) || !$connData[GC_CONNECTIONS_DB_PREFIX]) {
				$connData[GC_CONNECTIONS_DB_PREFIX] = '';
			}
			if(!isset($connData[GC_CONNECTIONS_DB_SID]) || !$connData[GC_CONNECTIONS_DB_SID]) {
				$connData[GC_CONNECTIONS_DB_SID] = false;
			}
			if(!isset($connData[GC_CONNECTIONS_DB_PASSWORD]) || !$connData[GC_CONNECTIONS_DB_PASSWORD]) {
				$connData[GC_CONNECTIONS_DB_PASSWORD] = false;
			}
			if(!isset($connData[GC_CONNECTIONS_DB_ENGINE]) || !isset($connData[GC_CONNECTIONS_DB_SERVER]) || !isset($connData[GC_CONNECTIONS_DB_NAME]) || !isset($connData[GC_CONNECTIONS_DB_USERNAME])) {
				unset($connData);
			}
		}
	}
}
