<?php

abstract class ErrorController extends Controller {
	//
	// Protected properties.
	/**
	 * @var boolean
	 */
	protected $_cached = false;
	/**
	 * @var Controller 
	 */
	protected $_failingController = null;
	//
	// Magic methods.
	//
	// Public methods.
	public function setFailingController(Controller $controller) {
		$this->_failingController = $controller;
	}
	//
	// Protected methods.
	protected function basicRun() {
		$out = true;

		if($this->_failingController !== null) {
			$this->assign("errorinfo", true);

			$this->assign("errors", $this->_failingController->errors());
			$this->assign("lasterror", $this->_failingController->lastError());
		} else {
			$this->assign("errorinfo", false);
		}

		return $out;
	}
}
