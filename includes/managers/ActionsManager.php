<?php

class ActionsManager extends UrlManager {
	//
	// Magic methods.
	//
	// Public methods.
	public function run($autoDisplay = true) {
		global $Defaults;

		$actionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : $Defaults["action"];

		$status = false;
		$controllerClass = $this->getController($actionName);
		if($controllerClass !== false) {
			$status = $controllerClass->run();
		}

		$lastRun = false;
		if($status) {
			$lastRun = $controllerClass->lastRun();
		} else {
			$errorActionName = HTTPERROR_NOT_FOUND;
			if($controllerClass !== false) {
				$lastError = $controllerClass->lastError();
				if($lastError) {
					$errorActionName = $lastError["code"];
				} else {
					$errorActionName = HTTPERROR_INTERNAL_SERVER_ERROR;
				}
			}

			$errorControllerClass = $this->getController($errorActionName);
			if($controllerClass !== false) {
				$errorControllerClass->setFailingController($controllerClass);
			}
			$errorControllerClass->run();
			$lastRun = $errorControllerClass->lastRun();
		}


		if($autoDisplay) {
			foreach($lastRun["headers"] as $name => $value) {
				header("{$name}: {$value}");
			}
			echo $lastRun["render"];
		}

		return $lastRun;
	}
	//
	// Protected methods.
	protected function getController($actionName) {
		$out = false;

		$controllerPath = Paths::Instance()->controllerPath($actionName);
		if(is_readable($controllerPath)) {
			require_once $controllerPath;

			$controllerClassName = (is_numeric($actionName) ? "N" : "").ucfirst($actionName)."Controller";
			$out = new $controllerClassName();
		}

		return $out;
	}
	protected function init() {
		parent::init();
	}
}
