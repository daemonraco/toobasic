<?php

/**
 * @class Singleton
 */
abstract class Singleton {
	//
	// Magic methods.
	/**
	 * Prevent users from directly creating the singleton's instance.
	 */
	protected function __constructor() {
		$this->init();
	}
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		trigger_error(get_called_class()."::".__FUNCTION__.": Clone is not allowed.", E_USER_ERROR);
	}
	//
	// Public methods.
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
