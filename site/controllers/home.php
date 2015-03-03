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

		return $out;
	}
}
