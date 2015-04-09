<?php

namespace TooBasic;

abstract class Model {
	//
	// Protected class properties.
	//
	// Protected properties.
	protected $_modelsFactory = false;
	protected $_isSingleton = true;
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

		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			
		}

		return $out;
	}
	//
	// Public methods.
	public function isSingleton() {
		return $this->_isSingleton;
	}
	//
	// Protected methods.
	abstract protected function init();
}
