<?php

/**
 * @class EnrouteController
 *
 * Accessible at '?action=enroute'
 */
class EnrouteController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
