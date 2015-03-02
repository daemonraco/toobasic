<?php

class ActionsManager extends UrlManager {
	//
	// Magic methods.
	//
	// Public methods.
	public function run() {
		global $Defaults;

		$actionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : $Defaults["action"];

		$actionPath = Paths::Instance()->controllerPath($actionName);
		require_once $actionPath;

		$actionClassName = ucfirst($actionName)."Controller";
		$actionClass = new $actionClassName();
		$actionClass->run();
	}
}
