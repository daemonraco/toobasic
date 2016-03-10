<?php

use TooBasic\Sanitizer;

class UriPathService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$source = urldecode($this->params->get->source);
		$this->assign('result', Sanitizer::UriPath($source));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'source';
	}
}
