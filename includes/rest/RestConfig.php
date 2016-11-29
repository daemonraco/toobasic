<?php

/**
 * @file RestConfig.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\MagicProp;
use TooBasic\Managers\RestManagerException;
use TooBasic\SpecsValidator;

/**
 * @class RestConfig
 * This class acts as interpreter and validator of RESTful configurations.
 */
class RestConfig extends Config {
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $name Configuration file name.
	 * @param string $mode Loading mechanims to use.
	 * @throws \TooBasic\Managers\RestManagerException
	 */
	public function __construct($name, $mode = GC_CONFIG_MODE_SIMPLE) {
		//
		// Running parent initializations.
		parent::__construct($name, $mode);
		//
		// Checking JSON against specifiations.
		if(!SpecsValidator::ValidateJsonString('rest', json_encode($this), $info)) {
			throw new RestManagerException(MagicProp::Instance()->tr->EX_rest_config_broken." {$info[JV_FIELD_ERROR][JV_FIELD_MESSAGE]}");
		}
	}
}
