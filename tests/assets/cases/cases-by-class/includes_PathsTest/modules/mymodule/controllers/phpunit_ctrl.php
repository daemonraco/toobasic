<?php

/**
 * @class HomeController
 *
 * Accessible at '?action=home'
 */
class HomeController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$this->setError(HTTPERROR_NOT_IMPLEMENTED, 'This is the wrong controller');
		return $this->status();
	}
}
