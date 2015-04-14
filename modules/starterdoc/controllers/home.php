<?php

class HomeController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Protected methods.
	protected function basicRun() {
		if(isset($this->params->get->example) && $this->params->get->example == "hellomodel") {
			$this->assign("currentexample", $this->params->get->example);
			$this->model->example->sayHi();
		}

		return true;
	}
	protected function init() {
		parent::init();

		$this->_cacheParams["GET"][] = "example";
	}
}
