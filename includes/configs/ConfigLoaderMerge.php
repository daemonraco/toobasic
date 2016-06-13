<?php

/**
 * @file ConfigLoaderMulti.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Configs;

//
// Class aliases.
use TooBasic\ConfigException;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class ConfigLoaderMulti
 * This class holds the logic to load a single config files in a simple way.
 */
class ConfigLoaderMerge extends ConfigLoader {
	//
	// Protected methods.
	/**
	 * This method loads all properties on a JSON file and add them as
	 * properties of the an object.
	 *
	 * @param string $confPath Absolute path of a JSON file.
	 * @param \TooBasic\Config $holder Config object to which add properties.
	 * @throws \TooBasic\ConfigException
	 */
	protected function mergeProperties($confPath) {
		//
		// Checking reading permissions.
		if(is_readable($confPath)) {
			//
			// Loading file contents and decoding it.
			$json = json_decode(file_get_contents($confPath));
			//
			// Checking JSON decoding.
			if($json) {
				//
				// Loading parameters and overring those that
				// already exist.
				foreach($json as $key => $value) {
					if(!preg_match('/^_mode$|^_name$|^_CP_.*/', $key)) {
						if(!isset($this->_config->{$key})) {
							$this->_config->{$key} = $value;
						} elseif(is_array($this->_config->{$key})) {
							if(!is_array($value)) {
								throw new ConfigException(Translate::Instance()->EX_array_expected_but_type(['type' => gettype($value)]));
							}
							$this->_config->{$key} = array_merge($this->_config->{$key}, $value);
						} elseif(is_object($this->_config->{$key})) {
							if(!is_object($value)) {
								throw new ConfigException(Translate::Instance()->EX_object_expected_but_type(['type' => gettype($value)]));
							}
							foreach($value as $subKey => $subValue) {
								$this->_config->{$key}->{$subKey} = $subValue;
							}
						} else {
							$this->_config->{$key} = $value;
						}
					}
				}
			} else {
				throw new ConfigException(Translate::Instance()->EX_JSON_invalid_file([
					'path' => $confPath,
					'errorcode' => json_last_error(),
					'error' => json_last_error_msg()
				]));
			}
		} else {
			throw new ConfigException("Unable to find/read configuration file '{$confPath}'");
		}
	}
	/**
	 * This method returns the list of config file paths to load.
	 *
	 * @return string[] Returns a list of absolute paths.
	 */
	protected function paths() {
		return Paths::Instance()->configPath($this->_config->name(), Paths::ExtensionJSON, true);
	}
}
