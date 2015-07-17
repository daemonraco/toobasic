<?php

namespace TooBasic;

/**
 * @class DBManager
 * This class centralizes access to databases connections.
 */
class DBManager extends Manager {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\DBAdapter[string] List of known database connections.
	 */
	protected $_connections = array();
	//
	// Magic methods.
	/**
	 * Easy way to obtain a databse connection based on its name.
	 *
	 * @param string $dbname Name for the database connection configuration to
	 * look for.
	 * @return \TooBasic\DBAdapter Returns a database connection.
	 */
	public function __get($dbname) {
		//
		// Forwarding call.
		return $this->get($dbname);
	}
	//
	// Public methods.
	/**
	 * This method to a database conneciton using a name. If the connection is
	 * not yet initialized, it gets connected and then returned.
	 *
	 * @param string $dbname Name for the database connection configuration to
	 * look for.
	 * @return \TooBasic\DBAdapter Returns a database connection.
	 * @throws \TooBasic\DBException
	 */
	public function get($dbname) {
		//
		// Avoiding reconnection to knwon connections.
		if(!isset($this->_connections[$dbname])) {
			//
			// Global dependencies.
			global $Connections;
			//
			// Checking if there's a configuration for the given name,
			// otherwise, and exception is thrown.
			if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
				global $Database;
				$engine = $Connections[GC_CONNECTIONS_DB][$dbname][GC_CONNECTIONS_DB_ENGINE];
				if(isset($Database[GC_DATABASE_DB_CONNECTION_ADAPTERS][$engine])) {
					$this->_connections[$dbname] = new $Database[GC_DATABASE_DB_CONNECTION_ADAPTERS][$engine]($dbname);
				} else {
					$this->_connections[$dbname] = new DBAdapter($dbname);
				}
			} else {
				throw new \TooBasic\DBException("There's no database connection configuration named '{$dbname}'");
			}
		}
		//
		// Returning requested database connection.
		return $this->_connections[$dbname];
	}
	/**
	 * Returns a database connection suitable for cache-in-database entries.
	 *
	 * @return \TooBasic\DBAdapter Returns a database connection.
	 */
	public function getCache() {
		return $this->get($this->getCacheName());
	}
	/**
	 * Returns a database connection name suitable for cache-in-database
	 * entries.
	 *
	 * @return string Returns a database connection name.
	 */
	public function getCacheName() {
		//
		// Global dependencies.
		global $Connections;
		//
		// Default name is the default database name.
		$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
		//
		// But, if there's an specific connection for cache-in-database,
		// it should be used.
		if(isset($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_CACHE])) {
			$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_CACHE];
		}

		return $name;
	}
	public function getDefault() {
		return $this->get($this->getDefaultName());
	}
	public function getDefaultName() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
	}
	public function getInstall() {
		return $this->get($this->getInstallName());
	}
	public function getInstallName() {
		global $Connections;

		$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
		if(isset($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_INSTALL])) {
			$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_INSTALL];
		}

		return $name;
	}
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_KEEPUNKNOWNS];
	}
}
