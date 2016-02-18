<?php

/**
 * @file ConfigLoader.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Configs;

//
// Class aliases.
use TooBasic\Config;

/**
 * @class ConfigLoader
 * @abstract
 * This abstract class represents all the basic logic required from a config files
 * loader, from which files to load to how to pick properties inside them and set
 * then set to a config object.
 */
abstract class ConfigLoader {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Config @TODO doc
	 */
	protected $_config = false;
	//
	// Magic methods.
	public function __construct(Config $config) {
		$this->_config = $config;
	}
	//
	// Public methods.
	public function load() {
		//
		// Getting the list of files to load based on current mechanism.
		$confPaths = $this->paths($this->_config->name());
		//
		// Checking if there's at least one file to load.
		if($confPaths) {
			//
			// Loading properties from each JSON file.
			foreach($confPaths as $confPath) {
				$this->mergeProperties($confPath);
			}
		} else {
			//
			// If there are no file to load is a fatal error.
			throw new ConfigException("Unable to find any configuration file named '{$this->name()}'");
		}
	}
	/**
	 * This method loads all properties on a JSON file and add them as
	 * properties of the an object.
	 *
	 * @param string $confPath Absolute path of a JSON file.
	 * @param \TooBasic\Config $holder Config object to which add properties.
	 * @throws \TooBasic\ConfigException
	 */
	public function mergeProperties($confPath) {
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
				foreach($json as $k => $v) {
					if(!preg_match('/^_mode$|^_name$|^_CP_.*/', $k)) {
						$this->_config->{$k} = $v;
					}
				}
			} else {
				throw new ConfigException("Wrong configuration file '{$confPath}' (".json_last_error_msg().')');
			}
		} else {
			throw new ConfigException("Unable to find/read configuration file '{$confPath}'");
		}
	}
	abstract public function paths($seed);
}
