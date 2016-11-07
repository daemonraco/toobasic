<?php

/**
 * @file Sanitizer.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Sanitizer
 * This class provides a centralized tool to sanitize paths and URLs.
 */
class Sanitizer {
	//
	// Magic methods.
	/**
	 * Prevent users from creating this class.
	 */
	protected function __construct() {
		trigger_error(get_called_class().'::'.__FUNCTION__.': Create is not allowed.', E_USER_ERROR);
	}
	/**
	 * Prevent users from clone this class.
	 */
	final public function __clone() {
		trigger_error(get_called_class().'::'.__FUNCTION__.': Clone is not allowed.', E_USER_ERROR);
	}
	//
	// Public class methods.
	/**
	 * This methods takes a directory path (real or not) and cleans any
	 * duplicated directory separator and also enforces the right separator
	 * based on the current O.S.
	 *
	 * @param string $path Path to clean.
	 * @return string Cleaned path.
	 */
	static public function DirPath($path) {
		//
		// Replacing directory separators by the right one for the current
		// O.S.
		$out = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
		//
		// Removing duplicates.
		while(strpos($out, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) !== false) {
			$out = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $out);
		}
		//
		// Returning a clean path.
		return $out;
	}
	/**
	 * This methods takes a URI and cleans any duplicated path separator and
	 * also enforces the slash as separator.
	 *
	 * @param string $uri URI to clean.
	 * @return string Cleaned URI.
	 */
	static public function UriPath($uri) {
		//
		// Replacing path separators by the slash.
		$out = str_replace(['\\', '/'], '/', $uri);
		//
		// Removing duplicates.
		while(strpos($out, '//') !== false) {
			$out = str_replace('//', '/', $out);
		}
		//
		// Returning a clean URI.
		return $out;
	}
}
