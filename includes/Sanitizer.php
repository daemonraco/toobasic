<?php

class Sanitizer {
	//
	// Magic methods.
	protected function __construct() {
		
	}
	//
	// Public class methods.
	static public function DirPath($path) {
		return str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $path);
	}
	static public function UriPath($path) {
		return str_replace(array("\\", "/"), "/", $path);
	}
}
