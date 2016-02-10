<?php

/**
 * @class HelloWorldService
 *
 * Accessible at '?service=hello_world'
 */
class HelloWorldService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('hello', 'world');

		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
