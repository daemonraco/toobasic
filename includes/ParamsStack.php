<?php

namespace TooBasic;

class ParamsStack {
	//
	// Protected properties.
	protected $_debugs = array();
	protected $_hasDebugs = false;
	protected $_params = array();
	//
	// Magic methods.
	public function __construct($list) {
		$this->_params = $list;
		//
		// Detecting debug parameters
		foreach($this->_params as $param => $value) {
			if(preg_match("/^debug([a-z0-9]*)$/", $param)) {
				$this->_hasDebugs = true;
				$this->_debugs[$param] = $value;
			}
		}
	}
	public function __get($name) {
		$out = null;

		if(isset($this->_params[$name])) {
			$out = $this->_params[$name];
		}

		return $out;
	}
	public function __isset($name) {
		return isset($this->_params[$name]);
	}
	//
	// Public properties.
	public function addValues($values) {
		foreach($values as $key => $value) {
			if(!is_numeric($key)) {
				$this->_params[$key] = $value;
			}
		}
	}
	public function all() {
		return $this->_params;
	}
	public function debugs() {
		return $this->_debugs;
	}
	public function hasDebugs() {
		return $this->_hasDebugs;
	}
}
