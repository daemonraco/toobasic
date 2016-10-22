<?php

/**
 * @file RestConfig.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use JSONValidator;
use TooBasic\MagicProp;
use TooBasic\Managers\RestManagerException;

/**
 * @class RestConfig
 */
class RestConfig extends Config {
	public function __construct($name, $mode = GC_CONFIG_MODE_SIMPLE) {
		parent::__construct($name, $mode);

		if(!self::GetValidator()->validate(json_encode($this), $info)) {
			throw new RestManagerException(MagicProp::Instance()->tr->EX_rest_config_broken." {$info[JV_FIELD_ERROR][JV_FIELD_MESSAGE]}");
		}
	}
	//
	// Protected class methods.
	/**
	 * This class method provides access to a JSONValidator object loaded for
	 * Simple API Reader specifications.
	 *
	 * @return \JSONValidator Returns a loaded validator.
	 */
	protected static function GetValidator() {
		//
		// Validators cache.
		static $validator = false;
		//
		// Checking if the validators is loaded.
		if(!$validator) {
			global $Directories;
			$validator = JSONValidator::LoadFromFile("{$Directories[GC_DIRECTORIES_SPECS]}/rest.json");
		}

		return $validator;
	}
}
