<?php

abstract class Model {
	//
	// Protected class properties.
	protected static $_IsSingleton = true;
	//
	// Protected properties.
	protected $_modelsFactory = false;
	//
	// Magic methods.
	public function __construct() {
		$this->_modelsFactory = ModelsFactory::Instance();
		$this->init();
	}
	/**
	 *  @todo doc
	 *
	 * @param type $prop @todo doc
	 * @return mixed @todo doc
	 */
	public function __get($prop) {
		$out = false;

		if($prop == "model") {
			$out = $this->_modelsFactory;
		}

		return $out;
	}
	//
	// Protected methods.
	abstract protected function init();
	//
	// Public class methods.
	public static function IsSingleton() {
		return self::$_IsSingleton;
	}
}
