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
			//
			// If there's a layout present, controller's result must
			// be shown inside a layout's result.
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

				throw new Exception("[DB-{$code}] {$error['message']}");
			}
			throw new Exception('There are database errors specs');
		} else {
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					throw new Exception('Database couldn\'t be upgraded');
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
			//
			// If there's a previous run, this must be the layout and
			// the previous run comes from the controller, so
			// assignments from that controller should be available
			// for the layout for different reason like setting the
			// page title.
			if(is_array($previousActionRun)) {
				/** @fixme There should be a way to change layout's cache key setting from the controller. */
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
			if(class_exists($controllerClassName)) {
				$out = new $controllerClassName($actionName);
			} else {
				throw new Exception("Class '{$controllerClassName}' is not defined. File '{$controllerPath}' doesn't seem to load the right object.");
			}
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
