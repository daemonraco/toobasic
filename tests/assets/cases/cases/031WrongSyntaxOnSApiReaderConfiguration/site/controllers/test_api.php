<?php

class TestApiController extends \TooBasic\Controller {
	protected $_cached = false;
	protected function basicRun() {
		$api = $this->sapireader->{$this->params->get->api};

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'api';
	}
}
