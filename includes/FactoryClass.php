<?php

namespace TooBasic;

//
// Class aliases.
use TooBasic\Exception;

/**
 * @abstract
 * @class FactoryClass
 * This is the basic representation for TooBasci of a class with only class
 * methods.
 */
abstract class FactoryClass {
	//
	// Magic methods.
	/**
	 * Prevent users from directly creating a factory's instance.
	 */
	protected function __constructor() {
		// It is a factory.
	}
	/**
	 * Prevent users from clonining a factory's instance if they found how to
	 * create one.
	 */
	final public function __clone() {
		throw new Exception('Clone is not allowed');
	}
}
