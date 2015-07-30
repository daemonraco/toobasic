<?php

namespace TooBasic\Adapters\View;

class XML extends BasicAdapter {
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
