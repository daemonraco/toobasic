<?php

/**
 * @class SubactionController
 *
 * Accessible at '?action=subaction'
 */
class SubactionController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
