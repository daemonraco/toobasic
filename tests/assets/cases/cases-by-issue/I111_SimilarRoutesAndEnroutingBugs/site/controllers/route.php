<?php

/**
 * @class RouteController
 *
 * Accessible at '?action=route'
 */
class RouteController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('value', $this->params->get->value);

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_cacheParams['GET'][] = 'value';
		$this->_requiredParams['GET'][] = 'value';
	}
}
