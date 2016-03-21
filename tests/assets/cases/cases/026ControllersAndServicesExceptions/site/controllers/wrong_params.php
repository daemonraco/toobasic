<?php

/**
 * @class WrongParamsController
 *
 * Accessible at '?action=wrong_params'
 */
class WrongParamsController extends \TooBasic\Controller {
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

		$this->_requiredParams['GET'][] = 'param';
	}
}
