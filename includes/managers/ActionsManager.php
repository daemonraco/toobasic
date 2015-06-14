<?php

namespace TooBasic;

class ActionsManager extends UrlManager {
	//
	// Public methods.
	/**
	 * 
	 * @global string $ActionName
	 * @param boolean $autoDisplay
	 * @return mixed 
	 */
	public function run($autoDisplay = true) {
		//
		// Global dependencies.
		global $ActionName;
		//
		// Default values.
		$layoutName = false;
		//
		// Current action execution.
		$actionLastRun = self::ExecuteAction($ActionName, null, $layoutName);
		//
		// Layout execution (if any).
		$layoutLastRun = false;
		//
		// Running layout's controller.
		if($layoutName) {
			$layoutLastRun = self::ExecuteAction($layoutName, $actionLastRun);
		}
		//
		// Displaying if required.
		if($autoDisplay) {
			$headers = array();
			if($layoutLastRun) {
				$headers = array_merge($headers, $layoutLastRun['headers']);
			}
			$headers = array_merge($headers, $actionLastRun['headers']);

			foreach($headers as $name => $value) {
				header("{$name}: {$value}");
			}

			if($layoutLastRun) {
				echo str_replace('%TOO_BASIC_ACTION_CONTENT%', $actionLastRun['render'], $layoutLastRun['render']);
			} else {
				echo $actionLastRun['render'];
			}
		}

		return $actionLastRun;
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();

		$dbStructureManager = DBStructureManager::Instance();
		if($dbStructureManager->hasErrors()) {
			foreach($dbStructureManager->errors() as $error) {
				$code = $error['code'];
				if(is_numeric($code)) {
					$code = sprintf('%03d', $code);
				}

				trigger_error("[DB-{$code}] {$error['message']}", E_USER_WARNING);
			}
			trigger_error('There are database errors specs', E_USER_ERROR);
		} else {
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					trigger_error('Database couldn\'t be upgraded', E_USER_ERROR);
				}
			}
		}
	}
	//
	// Public class methods.
	public static function ExecuteAction($actionName, $previousActionRun = null, &$layoutName = false) {
		//
		// Default values.
		$status = true;
		//
		// Loading controller based on current action name.
		$controllerClass = self::FetchController($actionName);
		if($controllerClass !== false) {
			$layoutName = $controllerClass->layout();

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
			$errorActionName = HTTPERROR_NOT_FOUND;
			if($controllerClass instanceof Exporter) {
				$lastError = $controllerClass->lastError();
				if($lastError) {
					$errorActionName = $lastError['code'];
				} else {
					$errorActionName = HTTPERROR_INTERNAL_SERVER_ERROR;
				}
			}

			$errorControllerClass = self::FetchController($errorActionName);
			if($controllerClass !== false) {
				$errorControllerClass->setFailingController($controllerClass);
			} else {
				$errorControllerClass->setErrorMessage("Unable to find action '{$actionName}'");
			}
			$layoutName = $errorControllerClass->layout();
			$errorControllerClass->run();
			$lastRun = $errorControllerClass->lastRun();
		}

		return $lastRun;
	}
	/**
	 * This class method looks for a controller based on an action name.
	 *
	 * @param string $actionName Action name from which guess a controller's
	 * name.
	 * @return \TooBasic\Controller Returns a controllers object or false on
	 * failure.
	 */
	public static function FetchController($actionName, $recursive = false) {
		//
		// Default values.
		$out = false;
		//
		// Looking for a controller with the given action name as a file
		// name.
		$controllerPath = Paths::Instance()->controllerPath($actionName);
		//
		// Checking the obtained path.
		if($controllerPath) {
			//
			// Loading physical file with the controllers definition.
			require_once $controllerPath;
			//
			// Guessing the right class name.
			$controllerClassName = (is_numeric($actionName) ? 'N' : '').\TooBasic\classname($actionName).GC_CLASS_SUFFIX_CONTROLLER;
			//
			// Creating the controllers class.
			$out = new $controllerClassName($actionName);
		} elseif(!$recursive) {
			//
			// If there's no controller file, but there's a template,
			// a virtual controller is used.
			//
			// Global dependecies.
			global $ModeName;
			//
			// Searching for a template.
			$template = Paths::Instance()->templatePath($actionName, $ModeName);
			//
			// Checking if there's a template for the given name.
			if($template) {
				//
				// Loading a virtual controller.
				$out = self::FetchController('too_basic_virtual', true);
				//
				// Setting view name to the virtual controller.
				if($out) {
					$out->setViewName($actionName);
				}
			}
		}
		//
		// Returning fetched controller.
		return $out;
	}
}
