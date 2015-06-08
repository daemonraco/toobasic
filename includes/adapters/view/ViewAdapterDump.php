<?php

namespace TooBasic;

class ViewAdapterDump extends ViewAdapterBasic {
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
