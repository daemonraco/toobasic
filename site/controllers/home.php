<?php

class HomeController extends Controller {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Public methods.
	protected function basicRun() {
		$out = false;

		$this->assign("test", "something");

		if(isset($_REQUEST["example"]) && $_REQUEST["example"] == "hellomodule") {
			$this->model->example->sayHi();
		}

		return $out;
	}
}
