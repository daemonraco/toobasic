<?php

class ParamsStack {
	//
	// Protected properties.
	protected $_params = array();
	//
	// Magic methods.
	public function __construct($list) {
		$this->_params = $list;
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
	// Protected properties.
	public function all() {
		return $this->_params;
	}
}
