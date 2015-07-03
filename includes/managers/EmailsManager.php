<?php

/**
 * @file EmailsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

class EmailsManager extends \TooBasic\Manager {
	//
	// Protected properties.
	protected $_emailName = false;
	//
	// Public methods.
	public function run($autoDisplay = true) {
		if($this->_emailName === false) {
			throw new \TooBasic\Exception("No email name set, use 'EmailsManager::Instance()->setEmail()'");
		}
		//
		// Default values.
		$layoutName = false;
		//
		// Current email execution.
		$emailLastRun = self::ExecuteAction($this->_emailName, null, $layoutName);
		//
		// Layout execution (if any).
		$layoutLastRun = false;
		//
		// Running layout's controller.
		if($layoutName) {
			$layoutLastRun = self::ExecuteAction($layoutName, $emailLastRun);
		}
		//
		// Displaying if required.
		if($autoDisplay) {
			//
			// If there's a layout present, controller's result must
			// be shown inside a layout's result.
			if($layoutLastRun) {
				echo str_replace('%TOO_BASIC_EMAIL_CONTENT%', $emailLastRun['render'], $layoutLastRun['render']);
			} else {
				echo $emailLastRun['render'];
			}
		}

		return $emailLastRun;
	}
	public function setEmail($emailName) {
		$this->_emailName = $emailName;
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();
	}
	//
	// Public class methods.
	public static function ExecuteAction($emailName, $previousActionRun = null, &$layoutName = false) {
		//
		// Default values.
		$status = true;
		//
		// Loading controller based on current email name.
		$controllerClass = self::FetchController($emailName);
		if($controllerClass !== false) {
			$layoutName = $controllerClass->layout();
			//
			// If there's a previous run, this must be the layout and
			// the previous run comes from the controller, so
			// assignments from that controller should be available
			// for the layout for different reason like setting the
			// page title.
			if(is_array($previousActionRun)) {
				$controllerClass->massiveAssign($previousActionRun['assignments']);
			}

			$status = $controllerClass->run();
		} else {
			$status = false;
		}

		$lastRun = false;
		if($status) {
			$lastRun = $controllerClass->lastRun();
		} else {
			$errorCode = HTTPERROR_NOT_FOUND;
			$errorMessage = "Unable to find email '{$emailName}'";
			if($controllerClass instanceof \TooBasic\Email) {
				$lastError = $controllerClass->lastError();
				if($lastError) {
					$errorCode = $lastError['code'];
					$errorMessage = $lastError['message'];
				} else {
					$errorCode = HTTPERROR_INTERNAL_SERVER_ERROR;
					$errorMessage = "Something went wrong with email '{$emailName}'";
				}
			}

			throw new \TooBasic\Exception($errorMessage, $errorCode);
		}

		return $lastRun;
	}
	/**
	 * This class method looks for a controller based on an email name.
	 *
	 * @param string $emailName Action name from which guess a controller's
	 * name.
	 * @return \TooBasic\Controller Returns a controllers object or false on
	 * failure.
	 */
	public static function FetchController($emailName, $recursive = false) {
		//
		// Default values.
		$out = false;
		//
		// Looking for a controller with the given email name as a file
		// name.
		$controllerPath = Paths::Instance()->emailControllerPath($emailName);
		//
		// Checking the obtained path.
		if($controllerPath) {
			//
			// Loading physical file with the controllers definition.
			require_once $controllerPath;
			//
			// Guessing the right class name.
			$controllerClassName = \TooBasic\Names::ControllerClass($emailName);
			//
			// Creating the controllers class.
			if(class_exists($controllerClassName)) {
				$out = new $controllerClassName($emailName);
			} else {
				throw new \TooBasic\Exception("Class '{$controllerClassName}' is not defined. File '{$controllerPath}' doesn't seem to load the right object.");
			}
		} elseif(!$recursive) {
			//
			// If there's no controller file, but there's a template,
			// a virtual controller is used.
			//
			// Searching for a template.
			$template = Paths::Instance()->templatePath($emailName, 'email');
			//
			// Checking if there's a template for the given name.
			if($template) {
				//
				// Loading a virtual controller.
				$out = self::FetchController('too_basic_virtual', true);
				//
				// Setting view name to the virtual controller.
				if($out) {
					$out->setViewName($emailName);
				}
			}
		}
		//
		// Returning fetched controller.
		return $out;
	}
}
