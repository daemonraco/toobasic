<?php

/**
 * @file Dump.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

/**
 * @class Dump
 * This view adapter provides a way to show current assignments as 'var_dump()'
 * would do.
 */
class Dump extends BasicAdapter {
	//
	// Protected properties.
	/**
	 * @var boolean This flag is TRUE when XDebug is installed as module of
	 * PHP.
	 */
	protected $_hasXDebug = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Detecting XDebug.
		$this->_hasXDebug = function_exists('xdebug_get_code_coverage');
		//
		// When XDebug is present, it won't be right to set 'text/plain'
		// as content type.
		if($this->_hasXDebug && isset($this->_headers['Content-Type'])) {
			unset($this->_headers['Content-Type']);
		}
	}
	//
	// Public methods.
	/**
	 * This method is the one in charge of rendering the output using a list
	 * of assignments and retruning it for further process.
	 *
	 * @param mixed[string] $assignments List of assignments to be analysed.
	 * @param string $template Provided for compatibility.
	 * @return string Retruns a view rendering result.
	 */
	public function render($assignments, $template) {
		//
		// Preparing to store in a buffer all that is prompted.
		ob_start();
		//
		// Rendering a clean list of assignments.
		var_dump($this->cleanRendering($assignments));
		//
		// Rescuing the outout and closing the buffer.
		$out = ob_get_contents();
		ob_end_clean();

		return $out;
	}
}
