<?php

class Starterdoc400Controller extends TooBasic\ErrorController {
	//
	// Protected properties
	protected $_cached = false;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign("title", $this->tr->HTTPERROR_400);
		return parent::basicRun();
	}
}
