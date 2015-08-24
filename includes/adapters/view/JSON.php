<?php

/**
 * @file JSON.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;
/**
 * @class JSON
 * This view adapter provides a way to show current assignments in JSON format.
 */
class JSON extends BasicAdapter {
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Setting a proper content type.
		$this->_headers['Content-Type'] = 'application/json';
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
		// Rendering a clean list of assignments and encoding it as JSON.
		return json_encode($this->cleanRendering($assignments));
	}
}
