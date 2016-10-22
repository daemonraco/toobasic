<?php

/**
 * @file Singleton.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Singleton
 * This is the basic representation of a singleton class inside TooBasic.
 */
abstract class Singleton {
	//
	// Magic methods.
	/**
	 * Prevent users from directly creating the singleton's instance.
	 */
	protected function __construct() {
		$this->init();
	}
	/**
	 * Prevent users from clone the singleton's instance.
	 */
	final public function __clone() {
		trigger_error(get_called_class().'::'.__FUNCTION__.': Clone is not allowed.', E_USER_ERROR);
	}
	//
	// Protected methods.
	/**
	 * Basic singleton initializer.
	 */
	protected function init() {
		
	}
	//
	// Public class methods.
	/**
	 * This method is the main access to a singleton and it always returns the
	 * single instance of this class.
	 *
	 * @return \TooBasic\Singleton Returns an instance of the required class.
	 */
	final public static function &Instance() {
		//
		// List of all known single instances associated to their class
		// name.
		static $Instances = array();
		//
		// Obtaining the current class name.
		$c = get_called_class();
		//
		// Checking if there's a single instance already created.
		if(!isset($Instances[$c])) {
			//
			// Creating the single instance.
			$Instances[$c] = new $c();
			//
			// Forcing the singleton to initialize.
			$Instances[$c]->init();
		}
		//
		// Retirning the single instance.
		return $Instances[$c];
	}
}
