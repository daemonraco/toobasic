<?php

class Starterdoc403Controller extends TooBasic\ErrorController {
	//
	// Protected properties
	protected $_cached = false;
	protected $_errorCode = HTTPERROR_FORBIDDEN;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign("title", $this->tr->HTTPERROR_403);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'starterdoc_httperror';
	}
}
