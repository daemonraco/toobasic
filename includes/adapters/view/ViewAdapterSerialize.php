<?php

namespace TooBasic;

class ViewAdapterSerialize extends ViewAdapterBasic {
	//
	// Public methods.
	public function render($assignments, $template = false) {
		return serialize($this->cleanRendering($assignments));
	}
}
