<?php

class HomeController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Protected methods.
	protected function basicRun() {
		$out = true;

		if(isset($_REQUEST["example"]) && $_REQUEST["example"] == "hellomodule") {
			$this->assign("currentexample", $_REQUEST["example"]);
			$this->model->example->sayHi();
		} else {
			$knownDebugs = array(
				"debugwithoutcache" => "",
				"debugresetcache" => "",
				"debugnolang" => "Disables all translations and prints keys.",
				"debugnolayout" => "Disables current layout if any."
			);
			ksort($knownDebugs);
			$this->assign("knowndebugs", $knownDebugs);
		}

		return $out;
	}
	protected function init() {
		parent::init();

		$this->_cacheParams["GET"][] = "example";
	}
}
