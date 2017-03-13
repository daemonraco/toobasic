<?php

class StarterdocController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		if(isset($this->params->get->example) && $this->params->get->example == "hellomodel") {
			$this->assign("currentexample", $this->params->get->example);
			$this->model->example->sayHi();
		} else {
			$this->assign("title", "Starter Doc");
		}

		return true;
	}
	protected function init() {
		parent::init();

		$this->_cacheParams["GET"][] = "example";
	}
}
