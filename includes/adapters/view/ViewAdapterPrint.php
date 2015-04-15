<?php

namespace TooBasic;

class ViewAdapterPrint extends ViewAdapterBasic {
	//
	// Public methods.
	public function render($assignments, $template = false) {
		return print_r($this->cleanRendering($assignments),true);
	}
}
