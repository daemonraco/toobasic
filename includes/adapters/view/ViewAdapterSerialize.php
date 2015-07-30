<?php

namespace TooBasic\Adapters\View;

class Serialize extends BasicAdapter {
	//
	// Public methods.
	public function render($assignments, $template) {
		return serialize($this->cleanRendering($assignments));
	}
}
