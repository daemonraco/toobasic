<?php

class HomeController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Public methods.
	protected function basicRun() {
		$out = true;

		if(isset($_REQUEST["example"]) && $_REQUEST["example"] == "hellomodule") {
			$this->assign("currentexample", $_REQUEST["example"]);
			$this->model->example->sayHi();
		} else {
			$knownDebugs = array(
				"debugwithoutcache" => "",
				"debugresetcache" => "",
				"debugnolang" => "Disables all translations and prints keys."
			);
			ksort($knownDebugs);
			$this->assign("knowndebugs", $knownDebugs);
		}

		return $out;
	}
	protected function init() {
		$this->_cacheParams["GET"][] = "example";
	}
}
