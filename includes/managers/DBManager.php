<?php

/**
 * @file DBManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases
use \TooBasic\DBException;

/**
 * @class DBManager
 * This class centralizes access to databases connections.
 */
class DBManager extends Manager {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter[string] List of known database connections.
	 */
	protected $_connections = [];
	//
	// Magic methods.
	/**
	 * Easy way to obtain a databse connection based on its name.
	 *
	 * @param string $dbname Name for the database connection configuration to
	 * look for.
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database connection.
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
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database connection.
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
					$this->_connections[$dbname] = new \TooBasic\Adapters\DB\Adapter($dbname);
				}
			} else {
				throw new DBException($this->tr->EX_DB_unknown_connection(['name' => $dbname]));
			}
		}
		//
		// Returning requested database connection.
		return $this->_connections[$dbname];
	}
	/**
	 * Returns a database connection suitable for cache-in-database entries.
	 *
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database connection.
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
		$name = $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB];
		//
		// But, if there's an specific connection for cache-in-database,
		// it should be used.
		if(isset($Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_CACHE])) {
			$name = $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_CACHE];
		}

		return $name;
	}
	/**
	 * This method provides access to current default database connection
	 * adapter.
	 *
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database connection.
	 */
	public function getDefault() {
		return $this->get($this->getDefaultName());
	}
	/**
	 * This method provides access to current default database connection
	 * name.
	 *
	 * @return string Returns a connection name.
	 */
	public function getDefaultName() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB];
	}
	/**
	 * This method provides access to current installacion database connection
	 * adapter.
	 *
	 * @return \TooBasic\Adapters\DB\Adapter Returns a database connection.
	 */
	public function getInstall() {
		return $this->get($this->getInstallName());
	}
	/**
	 * This method provides access to current installation database connection
	 * name.
	 *
	 * @return string Returns a connection name.
	 */
	public function getInstallName() {
		//
		// Global dependencies.
		global $Connections;
		//
		// Checking if there's a specific connection for installations,
		// otherwise, the default is used.
		$name = $this->getDefaultName();
		if(isset($Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_INSTALL])) {
			$name = $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_INSTALL];
		}

		return $name;
	}
	/**
	 * This method allows to know if unknown object inside a database should
	 * be kept or not.
	 *
	 * @return boolean Returns TRUE when unknown objects has to be kept.
	 */
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS];
	}
}
