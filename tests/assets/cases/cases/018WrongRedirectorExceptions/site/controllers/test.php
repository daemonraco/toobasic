<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Public methods.
	public function checkRedirectors() {
		return 'wrong-destination';
	}
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
	}
}
