<?php

class N500Controller extends TooBasic\ErrorController {
	//
	// Protected properties
	//
	// Public methods.
	//
	// Protected methods.
	protected function basicRun() {
		$out = parent::basicRun();

		return $out;
	}
	protected function init() {
		$this->_name = "500";
	}
}
