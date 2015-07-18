<?php

namespace TooBasic;

class ViewAdapterXML extends ViewAdapterBasic {
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();
		$this->_headers["Content-Type"] = 'application/xml';
	}
	//
	// Public methods.
	public function render($assignments, $template) {
		$out = \xmlrpc_encode($this->cleanRendering($assignments));

		return $out;
	}
}
