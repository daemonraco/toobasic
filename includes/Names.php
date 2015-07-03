<?php

namespace TooBasic;

class Names extends TooBasic\FactoryClass {
	//
	// Public class methods.
	public static function ControllerClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_CONTROLLER);
	}
	public static function EmailControllerClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_EMAIL_CONTROLLER);
	}
	//
	// Protected class methods.
	/**
	 * This function normalize class names.
	 *
	 * @param string $seed Name to be normalized.
	 * @return string Returns a normalized name.
	 */
	public static function ClassName($seed) {
		//
		// Default values.
		$out = (is_numeric($seed) ? 'N' : '').$seed;
		//
		// Cleaning special charaters
		$out = str_replace(array('_', '-', ':'), ' ', $out);
		$out = ucwords($out);
		$out = str_replace(' ', '', $out);
		//
		// Returning a clean name.
		return $out;
	}
	public static function ClassNameWithSuffix($seed, $suffix) {
		$out = self::ClassName($seed);
		if(!preg_match("/{$suffix}\$/", $out)) {
			$out .= $suffix;
		}
		return $out;
	}
}
