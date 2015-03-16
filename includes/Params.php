<?php

class Params extends Singleton {
	//
	// Constants.
	const TypeGET = "get";
	const TypePOST = "post";
	//
	// Protected properties.
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
			switch($_SERVER["REQUEST_METHOD"]) {
				case "POST":
					$out = $this->_paramsStacks[self::TypePOST];
					break;
				case "GET":
				default:
					$out = $this->_paramsStacks[self::TypeGET];
					break;
			}
		}

		return $out;
	}
	//
	// Public methods.
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
