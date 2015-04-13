<?php

namespace TooBasic;

class ViewAdapterJSON extends ViewAdapter {
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->_headers["Content-Type"] = "application/json";
	}
	//
	// Public methods.
	public function render($assignments, $template = false) {
		$out = json_encode(array_merge($this->_autoAssigns, $assignments));

		return $out;
	}
}
