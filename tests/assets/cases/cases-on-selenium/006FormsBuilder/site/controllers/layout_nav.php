<?php

/**
 * @class LayoutNavController
 *
 * Accessible adding '&action=layout_nav'
 */
class LayoutNavController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
	}
	protected function init() {
		parent::init();

//		$this->_cacheParams['GET'][] = 'someparam';
	}
}
