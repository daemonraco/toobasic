<?php

/**
 * @file Adapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters;

/**
 * @class Adapter 
 * @abstract
 * 
 * This is the most basic representation of an adapter in TooBasic.
 */
abstract class Adapter {
	//
	// Protected class properties.
	/**
	 * @var \TooBasic\Adapter[] List of loaded adapters. Avoids multiple
	 * instances.
	 */
	protected static $_Adapters = array();
	//
	// Magic methods.
	public function __construct() {
		
	}
	//
	// Protected mehtods.
	protected function init() {
		
	}
	//
	// Public class mehtods.
	/**
	 * Returns an adapter based on its name.
	 * 
	 * @param string $adapterName Adapters name.
	 * @return \TooBasic\Adapter Returns the requested adapter or false.
	 */
	public static function Factory($adapterName) {
		$out = false;
		//
		// Cheking if this adapter has already been loaded. If not, it is.
		// Adapters work as singletons.
		if(!isset(self::$_Adapters[$adapterName])) {
			self::$_Adapters[$adapterName] = new $adapterName();
			self::$_Adapters[$adapterName]->init();
			$out = self::$_Adapters[$adapterName];
		} else {
			$out = self::$_Adapters[$adapterName];
		}
		//
		// Returning found adapter.
		return $out;
	}
}
