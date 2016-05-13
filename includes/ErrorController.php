<?php

/**
 * @file ErrorController.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

use TooBasic\Managers\RoutesManager;

/**
 * @class ErrorController
 * @abstract
 * 
 * This abstract specification represents any controller used to show an error
 * pages, for example a HTTP-404 page.
 */
abstract class ErrorController extends Controller {
	//
	// Protected properties.
	/**
	 * @var boolean This kind of page should not be cached.
	 */
	protected $_cached = false;
	/**
	 * @var string Identifing HTTP error code for this controller. 
	 */
	protected $_errorCode = HTTPERROR_OK;
	/**
	 * @var \TooBasic\Controller Pointer to the controller that actually
	 * failed, if any.
	 */
	protected $_errorMessage = false;
	protected $_failingController = null;
	//
	// Public methods.
	public function setErrorMessage($message) {
		$this->_errorMessage = $message;
	}
	/**
	 * This method sets the controller that had problems and it's the reason
	 * for this controller/page.
	 * 
	 * @param \TooBasic\Controller $controller Controller with issues.
	 */
	public function setFailingController($controller) {
		$this->_failingController = $controller;
	}
	//
	// Protected methods.
	/**
	 * Main execution methods for any error controller/page.
	 * 
	 * @return boolean Retruns true when there was no problems generating
	 * assignments.
	 */
	protected function basicRun() {
		$out = true;
		//
		// Disabling error information.
		$this->assign('errorinfo', false);
		$this->assign('currenterror', null);
		$this->assign('errormessage', null);
		//
		// Checking for global errors.
		if(!$this->_failingController && !$this->_errorMessage) {
			//
			// Global dependencies.
			global $Defaults;
			//
			// Checking for routing errors.
			if($Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
				$routesError = RoutesManager::Instance()->lastErrorMessage();
				if($routesError) {
					$this->_errorMessage = $routesError;
				}
			}
		}
		//
		// Checking if there's a controller reporting problems.
		if($this->_failingController) {
			//
			// Setting error information to be shown.
			$this->assign('errorinfo', true);
			//
			// Setting error messages and codes.
			$this->assign('errors', $this->_failingController->errors());
			$this->assign('lasterror', $this->_failingController->lastError());
			//
			// Adding extra information about the current error.
			if($this->_errorCode != HTTPERROR_OK) {
				foreach($this->_failingController->errors() as $error) {
					if($error[GC_AFIELD_CODE] == $this->_errorCode) {
						$this->assign('currenterror', $error);
						break;
					}
				}
			} else {
				$this->assign('currenterror', $this->lasterror);
			}
		} elseif($this->_errorMessage) {
			//
			// Assigning a simple error message.
			$this->assign('errormessage', $this->_errorMessage);
		}

		return $out;
	}
}
