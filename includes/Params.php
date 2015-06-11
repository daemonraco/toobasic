<?php

namespace TooBasic;

class Params extends Singleton {
	//
	// Constants.
	const TypeCOOKIE = "cookie";
	const TypeENV = "env";
	const TypeGET = "get";
	const TypePOST = "post";
	const TypeSERVER = "server";
	//
	// Protected properties.
	protected $_debugs = false;
	protected $_hasDebugs = false;
	protected $_paramsStacks = array(
	);
	//
	// Magic methods.
	public function __get($name) {
		$out = null;

		$methodName = strtolower($name);
		if(isset($this->_paramsStacks[$methodName])) {
			$out = $this->_paramsStacks[$methodName];
		} else {
			foreach($this->_paramsStacks as $stack) {
				$out = $stack->{$name};
				if($out !== null) {
					break;
				}
			}
		}

		return $out;
	}
	public function __isset($name) {
		$out = false;

		$methodName = strtolower($name);
		if(isset($this->_paramsStacks[$methodName])) {
			$out = true;
		} else {
			foreach($this->_paramsStacks as $stack) {
				$out = isset($stack->{$name});
				if($out) {
					break;
				}
			}
		}

		return $out;
	}
	//
	// Public methods.
	/**
	 * 
	 * @param string $type
	 * @param string[string] $values
	 */
	public function addValues($type, $values) {
		if(isset($this->_paramsStacks[$type])) {
			$this->_paramsStacks[$type]->addValues($values);
		} else {
			trigger_error("Unknown parameters stack called '{$type}'", E_USER_ERROR);
		}
	}
	public function allOf($type) {
		$out = array();

		if(isset($this->_paramsStacks[$type])) {
			$out = $this->_paramsStacks[$type]->all();
		} else {
			trigger_error("Unknown parameters stack called '{$type}'", E_USER_ERROR);
		}

		return $out;
	}
	public function debugs() {
		if($this->_debugs === false) {
			$this->_debugs = array();
			foreach($this->_paramsStacks as $stack) {
				if($stack->hasDebugs()) {
					$this->_hasDebugs = true;
					$this->_debugs = array_merge($this->_debugs, $stack->debgus());
				}
			}
		}

		return $this->_debugs;
	}
	public function hasDebugs() {
		return $this->_hasDebugs;
	}
	//
	// Protected methods.
	protected function init() {
		$this->loadParams();
	}
	protected function loadParams() {
		$this->_paramsStacks[self::TypePOST] = new ParamsStack($_POST);
		$this->_paramsStacks[self::TypeGET] = new ParamsStack($_GET);
		$this->_paramsStacks[self::TypeENV] = new ParamsStack($_ENV);
		$this->_paramsStacks[self::TypeCOOKIE] = new ParamsStack($_COOKIE);
		$this->_paramsStacks[self::TypeSERVER] = new ParamsStack($_SERVER);
	}
}
