<?php

use TooBasic\Managers\RestManager;

class RestauthService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$auth = $this->params->get->auth;
		$type = $this->params->get->type;
		$mngr = RestManager::Instance();

		if($auth == 'login') {
			$mngr->authorize();
			$mngr->authorize($type);
			$this->assign('auth_key', $mngr->authorizationKey());
		} elseif($auth == 'logout') {
			$this->assign('result', $mngr->unauthorize());
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "'auth' should be either 'login' or 'logout'.");
		}

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_requiredParams['GET'][] = 'auth';
		$this->_requiredParams['GET'][] = 'type';
	}
}
