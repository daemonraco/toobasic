<?php

namespace TooBasic;

class ViewAdapterJSON extends ViewAdapterBasic {
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();
		$this->_headers["Content-Type"] = "application/json";
	}
	//
	// Public methods.
	public function render($assignments, $template) {
		return json_encode($this->cleanRendering($assignments));
	}
}
