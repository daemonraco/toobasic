<?php

class ActionsManager extends UrlManager {
	//
	// Magic methods.
	//
	// Public methods.
	public function run($autoDisplay = true) {
		global $Defaults;

		$actionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : $Defaults["action"];

		$controllerPath = Paths::Instance()->controllerPath($actionName);
		require_once $controllerPath;

		$controllerClassName = (is_numeric($actionName) ? "N" : "").ucfirst($actionName)."Controller";
		$controllerClass = new $controllerClassName();
		$controllerClass->run();

		$lastRun = $controllerClass->lastRun();

		if($autoDisplay) {
			echo $lastRun["render"];
		}

		return $lastRun["render"];
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();
	}
}
