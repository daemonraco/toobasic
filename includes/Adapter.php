<?php

class Adapter {
	//
	// Constants.
	//
	// Protected static properties.
	protected static $_Adapters = array();
	//
	// Protected properties.
	//
	// Magic methods.
	public function __construct() {
		
	}
	//
	// Public methods.
	//
	// Protected methods.
	//
	// Public class mehtods.
	public static function Factory($adapterName) {
		$out = false;

		if(!isset(self::$_Adapters[$adapterName])) {
			self::$_Adapters[$adapterName] = new $adapterName();
			$out = self::$_Adapters[$adapterName];
		}

		return $out;
	}
}
