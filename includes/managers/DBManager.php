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
	public function getCache() {
		return $this->get($this->getCacheName());
	}
	public function getCacheName() {
		global $Connections;

		$name = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
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
