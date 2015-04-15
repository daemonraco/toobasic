<?php

namespace TooBasic;

class DBManager extends Manager {
	//
	// Protected properties.
	protected $_connections = array();
	//
	// Magic methods.
	public function __get($dbname) {
		return $this->get($dbname);
	}
	//
	// Public methods.
	public function getCache() {
		global $Connections;

		$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
		if(isset($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_CACHE])) {
			$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_CACHE];
		}

		return $this->get($name);
	}
	public function getDefault() {
		global $Connections;
		return $this->get($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]);
	}
	public function get($dbname) {
		if(!isset($this->_connections[$dbname])) {
			global $Connections;

			if(isset($Connections[GC_CONNECTIONS_DB][$dbname])) {
				$this->_connections[$dbname] = new DBAdapter($dbname);
			}
		}

		$out = $this->_connections[$dbname];

		return $out;
	}
}
