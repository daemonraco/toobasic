<?php

use TooBasic\Sanitizer;

class DirPathService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$source = urldecode($this->params->get->source);
		$this->assign('result', Sanitizer::DirPath($source));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'source';
	}
}
