<?php

class Starterdoc500Controller extends TooBasic\ErrorController {
	//
	// Protected properties
	protected $_cached = false;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign("title", $this->tr->HTTPERROR_500);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'starterdoc_httperror';
	}
}
