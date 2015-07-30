<?php

/**
 * @file ViewAdapterDump.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

/**
 * @class Dump
 */
class Dump extends BasicAdapter {
	//
	// Protected properties.
	protected $_hasXDebug = false;
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();
		$this->_hasXDebug = function_exists('xdebug_get_code_coverage');
		if($this->_hasXDebug && isset($this->_headers['Content-Type'])) {
			unset($this->_headers['Content-Type']);
		}
	}
	//
	// Public methods.
	public function render($assignments, $template) {
		ob_start();

		var_dump($this->cleanRendering($assignments));

		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}
}
