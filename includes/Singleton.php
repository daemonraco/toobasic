<?php

if(!function_exists("get_called_class")) {
	function get_called_class() {
		$bt = debug_backtrace();
		$l = 0;
		do {
			$l++;
			$lines = file($bt[$l]["file"]);
			$callerLine = $lines[$bt[$l]["line"] - 1];
			preg_match("/([a-zA-Z0-9\_]+)::".$bt[$l]["function"]."/", $callerLine, $matches);
		} while($matches[1] === "parent" && $matches[1]);

		return $matches[1];
	}
}

/**
 * @class Singleton
 */
abstract class Singleton {
	//
	// Magic methods.
	protected function __constructor() {
		$this->init();
	}
	//
	// Public methods.
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		trigger_error(get_called_class()."::".__FUNCTION__.": Clone is not allowed.", E_USER_ERROR);
	}
	//
	// Protected methods.
	protected function init() {
		
	}
	//
	// Public class methods.
	final public static function &I() {
		return self::Instance();
	}
	final public static function &Instance() {
		static $Instances = array();

		$c = get_called_class();
		if(!isset($Instances[$c])) {
			$Instances[$c] = new $c();
			$Instances[$c]->init();
		}

		return $Instances[$c];
	}
}
