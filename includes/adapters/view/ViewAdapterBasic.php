<?php

namespace TooBasic;

abstract class ViewAdapterBasic extends ViewAdapter {
	//
	// Protected properties.
	protected $_noise = array(
		"tr",
		"ctrl"
	);
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();
		$this->_headers["Content-Type"] = "text/plain ";
	}
	//
	// Public methods.
	public function noise() {
		return $this->_noise;
	}
	//
	// Protected methods.
	protected function cleanRendering($assignments) {
		$merge = array_merge($this->_autoAssigns, $assignments);
		foreach($this->noise() as $key) {
			if(isset($merge[$key])) {
				$merge[$key] = "--REMOVED--";
			}
		}

		return $merge;
	}
}
