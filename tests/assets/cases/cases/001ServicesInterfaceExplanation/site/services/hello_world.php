<?php

/**
 * @class HelloWorldService
 *
 * Accessible at '?service=hello_world'
 */
class HelloWorldService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
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
