<?php

/**
 * @file ViewAdapterPrint.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ViewAdapterPrint
 */
class ViewAdapterPrint extends ViewAdapterBasic {
	//
	// Public methods.
	public function render($assignments, $template) {
		return print_r($this->cleanRendering($assignments), true);
	}
}
