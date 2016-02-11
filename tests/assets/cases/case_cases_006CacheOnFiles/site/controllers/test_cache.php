<?php

/**
 * @class TestCacheController
 *
 * Accessible at '?action=test_cache'
 */
class TestCacheController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$date = strftime('%H%M%S-').microtime(true);
		$this->assign('date', $date);

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_cacheParams['GET'][] = 'rand';
		$this->_requiredParams['GET'][] = 'rand';
	}
}
