<?php

/**
 * @file Color.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

/**
 * @class Color
 * This basic factory class provides a simple way to print colored text on a shell
 * interface.
 */
class Color extends \TooBasic\FactoryClass {
	//
	// Public class properties.
	/*
	 * @var bool When TRUE, this flag disables colors.
	 */
	public static $nocolor = false;
	//
	// Public class methods.
	/**
	 * This method applies a blue color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Blue($text) {
		return self::ApplyColor(34, $text);
	}
	/**
	 * This method applies a cyan color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Cyan($text) {
		return self::ApplyColor(36, $text);
	}
	/**
	 * This method applies a green color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Green($text) {
		return self::ApplyColor(32, $text);
	}
	/**
	 * This method applies a purple color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Purple($text) {
		return self::ApplyColor(35, $text);
	}
	/**
	 * This method applies a red color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Red($text) {
		return self::ApplyColor(31, $text);
	}
	/**
	 * This method applies a yellow color to a text.
	 *
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function Yellow($text) {
		return self::ApplyColor(33, $text);
	}
	//
	// Protected class properties.
	/**
	 * This method is the actual color formater for texts.
	 *
	 * @param int $colorId This is the shell color code to be applied.
	 * Ususally 33 for yellow, 31 for red, etc.
	 * @param string $text Text to be colored.
	 * @return string Returns a formated text.
	 */
	public static function ApplyColor($colorId, $text) {
		//
		// Appling color only when it's on a *nix environment and color
		// aren't disabled.
		return DIRECTORY_SEPARATOR == '/' && !self::$nocolor ? "\033[1;{$colorId}m{$text}\033[0m" : $text;
	}
}
