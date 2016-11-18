<?php

/**
 * @file SpecsValidator.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use JSONValidator;
use TooBasic\FactoryClass;

/**
 * @class SpecsValidator
 * @todo doc
 */
class SpecsValidator extends FactoryClass {
	//
	// Public class methods.
	/**
	 * @todo doc
	 *
	 * @param string $specsName @todo doc
	 * @param string $jsonString @todo doc
	 * @param mixed[string] $info Extra information about the validation.
	 * @param boolean $forced @todo doc
	 * @return boolean @todo doc
	 */
	public static function ValidateJsonString($specsName, $jsonString, &$info = false, $forced = false) {
		//
		// Default values.
		$valid = false;
		//
		// Global dependecies.
		global $Defaults;
		//
		// Checking installation status.
		if($forced || !$Defaults[GC_DEFAULTS_INSTALLED]) {
			$valid = self::GetValidatorFor($specsName)->validate($jsonString, $info);
		} else {
			//
			// When installed, this validation should always assume that everyting is ok, unless it is forces
			$valid = true;
			//
			// Forcing information to be empty.
			$info = false;
		}

		return $valid;
	}
	/**
	 * @todo doc
	 *
	 * @param string $specsName @todo doc
	 * @param string $path @todo doc
	 * @param mixed[string] $info Extra information about the validation.
	 * @param boolean $forced @todo doc
	 * @return boolean @todo doc
	 */
	public static function ValidateOnFile($specsName, $path, &$info = false, $forced = false) {
		//
		// Default values.
		$valid = false;
		//
		// Global dependecies.
		global $Defaults;
		//
		// Checking installation status.
		if($forced || !$Defaults[GC_DEFAULTS_INSTALLED]) {
			if(is_file($path) && is_readable($path)) {
				$valid = self::ValidateJsonString($specsName, file_get_contents($path), $info, $forced);
			}
		} else {
			//
			// When installed, this validation should always assume that everyting is ok, unless it is forces
			$valid = true;
			//
			// Forcing information to be empty.
			$info = false;
		}

		return $valid;
	}
	//
	// Protected class methods.
	/**
	 * This class method provides access to a JSONValidator object loaded for
	 * RESTful configurations.
	 *
	 * @return \JSONValidator Returns a loaded validator.
	 */
	protected static function GetValidatorFor($name) {
		//
		// Validators cache.
		static $validators = [];
		//
		// Checking if the validators is loaded.
		if(!isset($validators[$name])) {
			global $Directories;
			$validators[$name] = JSONValidator::LoadFromFile("{$Directories[GC_DIRECTORIES_SPECS]}/{$name}.json");
		}

		return $validators[$name];
	}
}
