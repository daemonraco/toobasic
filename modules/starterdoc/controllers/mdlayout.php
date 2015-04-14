<?php

class MdlayoutController extends TooBasic\Layout {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
	protected function init() {
		parent::init();
		$this->_cacheParams["GET"][] = "doc";
	}
}
