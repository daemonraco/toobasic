<?php

/**
 * @file ViewAdapterPrint.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

/**
 * @class Printr
 */
class Printr extends BasicAdapter {
	//
	// Public methods.
	public function render($assignments, $template) {
		return print_r($this->cleanRendering($assignments), true);
	}
}
