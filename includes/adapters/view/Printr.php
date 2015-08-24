<?php

/**
 * @file Printr.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

/**
 * @class Printr
 * This view adapter provides a way to show current assignments as 'print_r()'
 * would do.
 */
class Printr extends BasicAdapter {
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
		// Rendering a clean list of assignments.
		return print_r($this->cleanRendering($assignments), true);
	}
}
