<?php

/**
 * @file 404.php
 * @author Alejandro Dario Simi
 */

/**
 * @class N404Controller
 */
class N404Controller extends TooBasic\ErrorController {
	//
	// Protected properties.
	protected $_cached = false;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('title', $this->tr->HTTPERROR_404);
		$this->assign('toobasic_version', TOOBASIC_VERSION);
		return parent::basicRun();
	}
	protected function init() {
		$this->_viewName = 'toobasic_httperror';
	}
}
