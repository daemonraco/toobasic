<?php

use TooBasic\Names;

class NamesSuffixedService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$func = urldecode($this->params->get->func);
		$source = urldecode($this->params->get->source);
		$suffix = urldecode($this->params->get->suffix);
		$this->assign('result', Names::{$func}($source, $suffix));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'func';
		$this->_requiredParams['GET'][] = 'source';
		$this->_requiredParams['GET'][] = 'suffix';
	}
}
