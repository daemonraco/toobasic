<?php

class Starterdoc404Controller extends TooBasic\ErrorController {
	//
	// Protected properties
	protected $_cached = false;
	protected $_errorCode = HTTPERROR_NOT_FOUND;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign("title", $this->tr->HTTPERROR_404);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'starterdoc_httperror';
	}
}
