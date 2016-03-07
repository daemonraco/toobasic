<?php

class InternalService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->params->internal->someparam = urldecode($this->params->get->source);
		$this->assign('result', $this->params->internal->someparam);

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'source';
	}
}
