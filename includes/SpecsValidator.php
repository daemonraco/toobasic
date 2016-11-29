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
 * This class centralizes all JSON specification validations and consideres
 * current site being flagged as installed in which case all validations will
 * return TRUE unless it's forced to check.
 */
class SpecsValidator extends FactoryClass {
	//
	// Public class methods.
	/**
	 * It validates a JSON value given in a string.
	 *
	 * @param string $specsName Name of specification to check against.
	 * @param string $jsonString JSON string to validate.
	 * @param mixed[string] $info Extra information about the validation.
	 * @param boolean $forced When TRUE, it will ignored the installation flag
	 * of the current site and validate regardless.
	 * @return boolean Returns TRUE if all rules have been followed.
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
			//
			// Validating.
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
	 * It validates the contentes of a JSON file.
	 *
	 * @param string $specsName Name of specification to check against.
	 * @param string $path Absolute path where the JSON to validate is stored.
	 * @param mixed[string] $info Extra information about the validation.
	 * @param boolean $forced When TRUE, it will ignored the installation flag
	 * of the current site and validate regardless.
	 * @return boolean Returns TRUE if all rules have been followed.
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
			//
			// Checking file status.
			if(is_file($path) && is_readable($path)) {
				//
				// Forwarding validation.
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
	 * This class method provides access to a JSONValidator object based in
	 * its name.
	 *
	 * @param string $name Name of specification to load.
	 * @return \JSONValidator Returns a loaded validator.
	 */
	protected static function GetValidatorFor($name) {
		//
		// Validators cache.
		static $validators = [];
		//
		// Checking if the validator is loaded.
		if(!isset($validators[$name])) {
			global $Directories;
			$validators[$name] = JSONValidator::LoadFromFile("{$Directories[GC_DIRECTORIES_SPECS]}/{$name}.json");
		}

		return $validators[$name];
	}
}
