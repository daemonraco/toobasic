<?php

/**
 * @file ConfigLoader.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Configs;

//
// Class aliases.
use TooBasic\Config;
use TooBasic\ConfigException;
use TooBasic\Translate;

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
	 * @var \TooBasic\Config Config object shortcut.
	 */
	protected $_config = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param \TooBasic\Config $config Config object to load into.
	 */
	public function __construct(Config $config) {
		$this->_config = $config;
	}
	//
	// Public methods.
	/**
	 * This method load the proper list of config files.
	 *
	 * @throws ConfigException
	 */
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
			throw new ConfigException(Translate::Instance()->EX_unable_to_find_config_file_named(['name' => $this->_config->name()]));
		}
	}
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
				foreach($json as $k => $v) {
					if(!preg_match('/^_mode$|^_name$|^_CP_.*/', $k)) {
						$this->_config->{$k} = $v;
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
			throw new ConfigException(Translate::Instance()->EX_unable_to_find_config_file(['path' => $confPath]));
		}
	}
	/**
	 * This method returns the list of config file paths to load.
	 *
	 * @return string[] Returns a list of absolute paths.
	 */
	abstract protected function paths();
}
