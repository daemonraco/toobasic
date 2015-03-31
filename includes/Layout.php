<?php

namespace TooBasic;

abstract class Layout extends Controller {
	//
	// Protected properties.
	//
	// Magic methods.
	//
	// Public methods.
	public function layout() {
		return false;
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();

		$this->_cacheParams["GET"][] = "action";
		$this->_cacheParams["GET"][] = "mode";
	}
}
