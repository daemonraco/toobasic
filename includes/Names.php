<?php

namespace TooBasic;

class Names extends FactoryClass {
	//
	// Public class methods.
	public static function ConfigClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_CONFIG);
	}
	public static function ConfigFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function ControllerClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_CONTROLLER);
	}
	public static function ControllerFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function EmailControllerClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_EMAIL_CONTROLLER);
	}
	public static function EmailControllerFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function ItemRepresentationClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_REPRESENTATION);
	}
	public static function ItemRepresentationFilename($seed) {
		return self::Filename(self::ItemRepresentationClass($seed));
	}
	public static function ItemsFactoryClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_FACTORY);
	}
	public static function ItemsFactoryFilename($seed) {
		return self::Filename(self::ItemsFactoryClass($seed));
	}
	public static function ModelClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_MODEL);
	}
	public static function ModelFilename($seed) {
		return self::Filename($seed);
	}
	public static function ServiceClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_SERVICE);
	}
	public static function ServiceFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function ShellCronClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_CRON);
	}
	public static function ShellCronFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function ShellSystoolClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_SYSTOOL);
	}
	public static function ShellSysFilename($seed) {
		return self::SnakeFilename($seed);
	}
	public static function ShellToolClass($seed) {
		return self::ClassNameWithSuffix($seed, GC_CLASS_SUFFIX_TOOL);
	}
	public static function ShellToolFilename($seed) {
		return self::SnakeFilename($seed);
	}
	//
	// Generic public class methods.
	/**
	 * This function normalize class names.
	 *
	 * @param string $seed Name to be normalized.
	 * @return string Returns a normalized name.
	 */
	public static function ClassName($seed) {
		//
		// Cleaning unneeded prefixes.
		$seed = ltrim($seed, '\\');
		//
		// Namespace extraction.
		$parts = explode('\\', $seed);
		$class = array_pop($parts);
		$namespace = '';
		if(count($parts) > 0) {
			$namespace = implode('\\', $parts).'\\';
		}
		//
		// Default values.
		$out = (is_numeric($class) ? 'N' : '').$class;
		//
		// Cleaning special charaters
		$out = preg_replace('/([_\-:]+)/', ' ', $out);
		$out = ucwords($out);
		$out = str_replace(' ', '', $out);
		//
		// Returning a clean name.
		return $namespace.$out;
	}
	public static function ClassNameWithSuffix($seed, $suffix) {
		$out = self::ClassName($seed);
		if(!preg_match("/{$suffix}\$/", $out)) {
			$out .= $suffix;
		}
		return $out;
	}
	public static function Filename($seed) {
		$out = explode('\\', $seed);
		$out = array_pop($out);
		//
		// Cleaning special charaters
		$out = preg_replace('/([_\-:]+)/', ' ', $out);
		$out = ucwords($out);
		$out = str_replace(' ', '', $out);

		return $out;
	}
	public static function SnakeFilename($seed) {
		$out = explode('\\', $seed);
		$out = array_pop($out);
		//
		// Camelcase to snakecase.
		$out = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $out));
		//
		// Cleaning special charaters.
		$out = preg_replace('/([ ]+)/', '-', $out);

		return $out;
	}
}
