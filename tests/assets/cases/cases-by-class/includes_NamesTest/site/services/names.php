<?php

use TooBasic\Names;

class NamesService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$func = urldecode($this->params->get->func);
		$source = urldecode($this->params->get->source);
		$this->assign('result', Names::{$func}($source));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'func';
		$this->_requiredParams['GET'][] = 'source';
	}
}
