<?php

/**
 * @file 501.php
 * @author Alejandro Dario Simi
 */

/**
 * @class N501Controller
 */
class N501Controller extends TooBasic\ErrorController {
	//
	// Protected properties.
	protected $_cached = false;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('title', $this->tr->HTTPERROR_501);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'toobasic_httperror';
	}
}
