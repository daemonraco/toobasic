<?php

namespace TooBasic;

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
	public function setFailingController(Controller $controller) {
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
		// Checking if there's a controller reporting problems.
		if($this->_failingController !== null) {
			//
			// Setting error information to be shown.
			$this->assign('errorinfo', true);
			//
			// Setting error messages and codes.
			$this->assign('errors', $this->_failingController->errors());
			$this->assign('lasterror', $this->_failingController->lastError());
			//
			// Adding extra information about the current error.
			$this->assign('currenterror', null);
			foreach($this->_failingController->errors() as $error) {
				if($error['code'] == $this->_name) {
					$this->assign('currenterror', $error);
					break;
				}
			}
		} elseif($this->_errorMessage) {
			//
			// Assigning a simple error message.
			$this->assign('errormessage', $this->_errorMessage);
		}

		return $out;
	}
}
