<?php

/**
 * @file Sanitizer.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

class Sanitizer {
	//
	// Magic methods.
	/**
	 * Prevent users from creating this class.
	 */
	protected function __construct() {
		trigger_error(get_called_class()."::".__FUNCTION__.": Create is not allowed.", E_USER_ERROR);
	}
	/**
	 * Prevent users from clone this class.
	 */
	final public function __clone() {
		trigger_error(get_called_class()."::".__FUNCTION__.": Clone is not allowed.", E_USER_ERROR);
	}
	//
	// Public class methods.
	static public function DirPath($path) {
		$out = str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $path);
		while(strpos($out, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false) {
			$out = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $out);
		}
		return $out;
	}
	static public function UriPath($path) {
		$out = str_replace(array("\\", "/"), "/", $path);
		while(strpos($out, "//") !== false) {
			$out = str_replace("//", "/", $out);
		}
		return $out;
	}
}
