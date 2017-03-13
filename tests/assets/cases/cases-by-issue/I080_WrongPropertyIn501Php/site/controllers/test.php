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
	// Protected methods.
	protected function basicRun() {
		$code = HTTPERROR_NOT_IMPLEMENTED;
		if(isset($this->params->get->code)) {
			$code = $this->params->get->code;
		}

		$this->setError($code, 'This is the wrong controller');
		return $this->status();
	}
}
