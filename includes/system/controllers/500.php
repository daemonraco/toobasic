<?php

/**
 * @file 500.php
 * @author Alejandro Dario Simi
 */

/**
 * @class N500Controller
 */
class N500Controller extends TooBasic\ErrorController {
	//
	// Protected properties.
	protected $_cached = false;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('title', $this->tr->HTTPERROR_500);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'toobasic_httperror';
	}
}
