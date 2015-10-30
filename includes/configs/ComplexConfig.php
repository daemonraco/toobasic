<?php

/**
 * @file ComplexConfig.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\ConfigException;

/**
 * @class ComplexConfig
 * @abstract
 * This abstract class can can represent a configuration file that checks for
 * required property paths and their values.
 * It must be inherited and the core property $_CP_RequiredPaths must be filled.
 */
abstract class ComplexConfig extends Config {
	//
	// Constants.
	const PathTypeAny = 'any';
	const PathTypeList = 'list';
	const PathTypeNumeric = 'numeric';
	const PathTypeObject = 'object';
	const PathTypeString = 'string';
	//
	// Protected core properties.
	/**
	 * @var string[string] Assocative list of required property paths and
	 * their types. For example:
	 * 	'parent->subproperty->somevalue' => \TooBasic\ComplexConfig::PathTypeList
	 */
	protected $_CP_RequiredPaths = array();
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $name Configuration file name (without extension).
	 * @param string $mode Mechanisme to use when loading.
	 * @throws \TooBasic\ConfigException
	 */
	public function __construct($name, $mode = self::ModeSimple) {
		parent::__construct($name, $mode);
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking installation status.
		if(!$Defaults[GC_DEFAULTS_INSTALLED]) {
			//
			// Checking structure.
			$this->checkRequiredPaths();
		}
	}
	//
	// Protected methods.
	/**
	 * This method checks the existence of required property types and their
	 * types.
	 *
	 * @throws \TooBasic\ConfigException
	 */
	public function checkRequiredPaths() {
		//
		// Default values.
		$basicErrorMessage = "Wrong configuration structure in '{$this->name()}'. ";
		//
		// Checking each path.
		foreach($this->_CP_RequiredPaths as $path => $type) {
			//
			// Checking existence.
			$exists = false;
			eval("\$exists=isset(\$this->{$path});");
			if(!$exists) {
				throw new ConfigException("{$basicErrorMessage}Path '->{$path}' is not present");
			} else {
				//
				// Checking property's type.
				$rightType = null;
				switch($type) {
					case self::PathTypeList:
						eval("\$rightType=is_array(\$this->{$path});");
						if(!$rightType) {
							throw new ConfigException("{$basicErrorMessage}Path '->{$path}' is not a list");
						}
						break;
					case self::PathTypeNumeric:
						eval("\$rightType=is_numeric(\$this->{$path});");
						if(!$rightType) {
							throw new ConfigException("{$basicErrorMessage}Path '->{$path}' is not numeric");
						}
						break;
					case self::PathTypeObject:
						eval("\$rightType=is_object(\$this->{$path});");
						if(!$rightType) {
							throw new ConfigException("{$basicErrorMessage}Path '->{$path}' is not an object");
						}
						break;
					case self::PathTypeString:
						eval("\$rightType=is_string(\$this->{$path});");
						if(!$rightType) {
							throw new ConfigException("{$basicErrorMessage}Path '->{$path}' is not a string");
						}
						break;
					case self::PathTypeAny:
						//
						// No controls here.
						break;
					default:
						throw new ConfigException("{$basicErrorMessage}Unhandled path type '{$type}'");
				}
			}
		}
	}
}
