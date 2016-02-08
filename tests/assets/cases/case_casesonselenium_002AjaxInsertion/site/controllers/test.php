<?php

/**
 * @class TestController
 *
 * Accessible at '?action=home'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false; #\TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('ajaxActionName', $this->params->get->atest);

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_requiredParams['GET'][] = 'atest';
	}
}
