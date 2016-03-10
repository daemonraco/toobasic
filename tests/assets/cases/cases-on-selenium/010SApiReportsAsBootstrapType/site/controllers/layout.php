<?php

/**
 * @class LayoutController
 *
 * Accessible adding '&layout=layout'
 */
class LayoutController extends \TooBasic\Layout {
	//
	// Protected properties
	protected $_cached = false;
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
