<?php

/**
 * @file 401.php
 * @author Alejandro Dario Simi
 */

/**
 * @class N401Controller
 */
class N401Controller extends TooBasic\ErrorController {
	//
	// Protected properties.
	protected $_cached = false;
	protected $_errorCode = HTTPERROR_UNAUTHORIZED;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('title', $this->tr->HTTPERROR_401);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'toobasic_httperror';
	}
}
