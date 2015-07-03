<?php

/**
 * @file Color.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

/**
 * @class Color
 */
class Color extends FactoryClass {
	//
	// Public class properties.
	public static $nocolor = false;
	//
	// Public class methods.
	public static function Blue($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;34m{$text}\033[0m" : $text;
	}
	public static function Cyan($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;36m{$text}\033[0m" : $text;
	}
	public static function Green($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;32m{$text}\033[0m" : $text;
	}
	public static function Purple($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;35m{$text}\033[0m" : $text;
	}
	public static function Red($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;31m{$text}\033[0m" : $text;
	}
	public static function Yellow($text) {
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;33m{$text}\033[0m" : $text;
	}
}
