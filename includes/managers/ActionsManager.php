<?php

namespace TooBasic;

class ActionsManager extends UrlManager {
	//
	// Protected properties.
	protected $_usesLayout = false;
	//
	// Magic methods.
	//
	// Public methods.
	public function run($autoDisplay = true) {
		global $ActionName;
		global $LayoutName;
		//
		// Current action execution.
		$actionLastRun = self::ExecuteAction($ActionName);
		//
		// Layout execution (if any).
		$layoutLastRun = false;
		if($this->usesLayout()) {
			$layoutLastRun = self::ExecuteAction($LayoutName);
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
	public function usesLayout() {
		return $this->_usesLayout;
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();

		global $LayoutName;

		if($LayoutName && !isset($this->_params->debugnolayout)) {
			$this->_usesLayout = true;
		}
	}
	//
	// Public class methods.
	public static function ExecuteAction($actionName) {
		$status = true;

		$controllerClass = self::FetchController($actionName);
		if($controllerClass !== false) {
			$status = $controllerClass->run();
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

			$controllerClassName = (is_numeric($actionName) ? "N" : "").ucfirst($actionName)."Controller";
			$out = new $controllerClassName($actionName);
		}

		return $out;
	}
	public static function InsertAction($actionName) {
		$lastRun = self::ExecuteAction($actionName);
		return $lastRun["render"];
	}
}
