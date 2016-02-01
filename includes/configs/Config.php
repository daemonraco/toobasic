<?php

/**
 * @file Config.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ConfigException
 * This exeption is thrown whenever 'Config' or 'ConfigsManager' finds a halting
 * error.
 */
class ConfigException extends Exception {
	
}

/**
 * @class Config
 * This class represent a JSON file and its properties providing a simpler access
 * and a controlled and easier loading mechanism.
 */
class Config extends \stdClass {
	//
	// Constants.
	const ModeSimple = 'simple';
	const ModeMultiple = 'multi';
	//
	// Protected properties.
	/**
	 * @var string This flag shows what mechanism was used to load a config
	 * file.
	 */
	protected $_mode = false;
	/**
	 * @var string This is the loaded configuration file name without
	 * extension.
	 */
	protected $_name = false;
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
		//
		// Storing basic values.
		$this->_name = $name;
		$this->_mode = $mode;
		//
		// Checking that the loading mechanism is a known one.
		if(!in_array($this->_mode, [self::ModeSimple, self::ModeMultiple])) {
			throw new ConfigException("Unknown mode '{$this->mode()}'");
		}
		//
		// Loading physical files.
		$this->load();
	}
	//
	// Public methods.
	/**
	 * This method provides access to the mechanism name used to load.
	 *
	 * @return string Returns a mechanism name.
	 */
	public function mode() {
		return $this->_mode;
	}
	/**
	 * This method provides access to the loaded configuration file name.
	 *
	 * @return string Returns a file name without extension.
	 */
	public function name() {
		return $this->_name;
	}
	//
	// Protected methods.
	/**
	 * This method is the one in charge of loading files using the proper
	 * mechanism.
	 * @throws \TooBasic\ConfigException
	 */
	protected function load() {
		//
		// Paths manager shortcut.
		$pathsManager = Paths::Instance();
		//
		// Getting the list of files to load based on current mechanism.
		$confPaths = [];
		if($this->mode() == self::ModeSimple) {
			$aux = $pathsManager->configPath($this->name(), Paths::ExtensionJSON);
			if($aux) {
				$confPaths[] = $aux;
			}
		} elseif($this->mode() == self::ModeMultiple) {
			$confPaths = $pathsManager->configPath($this->name(), Paths::ExtensionJSON, true);
		}
		//
		// Checking if there's at least one file to load.
		if($confPaths) {
			//
			// Loading properties from each JSON file.
			foreach($confPaths as $confPath) {
				$this->loadProperties($confPath);
			}
		} else {
			//
			// If there are no file to load is a fatal error.
			throw new ConfigException("Unable to find any configuration file named '{$this->name()}'");
		}
	}
	/**
	 * This method loads all properties on a JSON file as properties of the
	 * current object.
	 *
	 * @param string $confPath Absolute path of a JSON file.
	 * @throws \TooBasic\ConfigException
	 */
	protected function loadProperties($confPath) {
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
						$this->{$k} = $v;
					}
				}
			} else {
				throw new ConfigException("Wrong configuration file '{$confPath}' (".json_last_error_msg().')');
			}
		} else {
			throw new ConfigException("Unable to find/read configuration file '{$confPath}'");
		}
	}
}
