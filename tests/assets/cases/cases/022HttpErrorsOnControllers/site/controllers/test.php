<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->setError($this->params->get->code, 'CONTROLLED ERROR');
		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'code';
	}
}
