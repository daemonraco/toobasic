<?php

namespace TooBasic;

class Params extends Singleton {
	//
	// Constants.
	const TypeGET = "get";
	const TypePOST = "post";
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
	}
}
