<?php

namespace TooBasic;

abstract class Manager extends Singleton {
	//
	// Protected properties.
	protected $_params = null;
	//
	// Magic methods.
	//
	// Public methods.
	//
	// Protected methods.
	protected function init() {
		parent::init();

		$this->_params = Params::Instance();
	}
}
