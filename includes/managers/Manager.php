<?php

/**
 * @file Manager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use \TooBasic\MagicProp;
use \TooBasic\MagicPropException;

/**
 * @class Manager
 * @abstract
 * This is the basic abstraction of a manager.
 */
abstract class Manager extends \TooBasic\Singleton {
	//
	// Magic methods.
	/**
	 * This magic method provides a shortcut for magicprops
	 *
	 * @param string $prop Name of the magic property to look for.
	 * @return mixed Returns the requested magic property or FALSE if it was
	 * not found.
	 */
	public function __get($prop) {
		//
		// Default values.
		$out = false;
		//
		// Looking for the requested property.
		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			//
			// Ignored to avoid unnecessary issues.
		}

		return $out;
	}
}
