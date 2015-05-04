<?php

namespace TooBasic;

class ActionsManager extends UrlManager {
	//
	// Public methods.
	public function run($autoDisplay = true) {
		global $ActionName;

		$layoutName = false;
		//
		// Current action execution.
		$actionLastRun = self::ExecuteAction($ActionName, null, $layoutName);
		//
		// Layout execution (if any).
		$layoutLastRun = false;
		if($layoutName) {
			$layoutLastRun = self::ExecuteAction($layoutName, $actionLastRun);
		}
		//
		// Displaying if required.
		if($autoDisplay) {
			$headers = array();
			if($layoutLastRun) {
				$headers = array_merge($headers, $layoutLastRun["headers"]);
			}
			$headers = array_merge($headers, $actionLastRun["headers"]);

			foreach($headers as $name => $value) {
				header("{$name}: {$value}");
			}

			if($layoutLastRun) {
				echo str_replace("%TOO_BASIC_ACTION_CONTENT%", $actionLastRun["render"], $layoutLastRun["render"]);
			} else {
				echo $actionLastRun["render"];
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
				$code = $error["code"];
				if(is_numeric($code)) {
					$code = sprintf("%03d", $code);
				}

				trigger_error("[DB-{$code}] {$error["message"]}", E_USER_WARNING);
			}
			trigger_error("There are database errors specs", E_USER_ERROR);
		} else {
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					trigger_error("Database couldn't be upgraded", E_USER_ERROR);
				}
			}
		}
	}
	//
	// Public class methods.
	public static function ExecuteAction($actionName, $previousActionRun = null, &$layoutName = false) {
		$status = true;

		$controllerClass = self::FetchController($actionName);
		if($controllerClass !== false) {
			$layoutName = $controllerClass->layout();

			if(is_array($previousActionRun)) {
				$controllerClass->massiveAssign($previousActionRun["assignments"]);
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
					$errorActionName = $lastError["code"];
				} else {
					$errorActionName = HTTPERROR_INTERNAL_SERVER_ERROR;
				}
			}

			$errorControllerClass = self::FetchController($errorActionName);
			if($controllerClass !== false) {
				$errorControllerClass->setFailingController($controllerClass);
			}
			$layoutName = $errorControllerClass->layout();
			$errorControllerClass->run();
			$lastRun = $errorControllerClass->lastRun();
		}

		return $lastRun;
	}
	public static function FetchController($actionName) {
		$out = false;

		$controllerPath = Paths::Instance()->controllerPath($actionName);
		if(is_readable($controllerPath)) {
			require_once $controllerPath;

			$controllerClassName = (is_numeric($actionName) ? "N" : "").\TooBasic\classname($actionName).GC_CLASS_SUFFIX_CONTROLLER;
			$out = new $controllerClassName($actionName);
		}

		return $out;
	}
}
