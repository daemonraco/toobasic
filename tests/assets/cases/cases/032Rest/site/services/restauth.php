<?php

use TooBasic\Managers\RestManager;

/**
 * @class RestauthService
 *
 * Accessible at '?service=restauth'
 */
class RestauthService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$auth = $this->params->get->auth;
		if($auth == 'login') {
			$this->assign('hash', RestManager::Instance()->authorize());
		} elseif($auth == 'logout') {
			$this->assign('result', RestManager::Instance()->unauthorize());
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "'auth' should be either 'login' or 'logout'.");
		}

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_requiredParams['GET'][] = 'auth';
	}
}
