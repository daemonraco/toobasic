<?php

/**
 * @class SubactionController
 *
 * Accessible at '?action=subaction'
 */
class SubactionController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
