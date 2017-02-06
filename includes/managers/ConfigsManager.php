<?php

/**
 * @file ConfigsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Names;

/**
 * @class ConfigsManager
 * This singleton is the one in charge of providing a centralize access to all
 * JSON configuration files.
 */
class ConfigsManager extends Manager {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Config[string][string] List of loaded configration JSON
	 * files separated by the mechanism they where loaded.
	 */
	protected $_configs = [];
	//
	// Magic methods.
	/**
	 * This method provides an easy access to a configuration JSON file just
	 * by give its name and assuming it will load the first file found with
	 * such name.
	 *
	 * @param string $name JSON configuration file name.
	 * @return \TooBasic\Config Returns a configuration file.
	 * @throws \TooBasic\ConfigException
	 */
	public function __get($name) {
		return $this->get($name, GC_CONFIG_MODE_SIMPLE, false);
	}
	/**
	 * This method is similar to '__get()' but it also allows to specify the
	 * mechanism in which a file is loaded:
	 * 	- GC_CONFIG_MODE_SIMPLE: The first one found with a certain name.
	 * 	- GC_CONFIG_MODE_MULTI: Each one found with a certain name.
	 *
	 * @param string $name JSON configuration file name.
	 * @param mixed[] $args List of parameter given on the call. The first
	 * entry is used as a mechanism name and the second as a namespace name.
	 * @return \TooBasic\Config Returns a configuration file.
	 * @throws \TooBasic\ConfigException
	 */
	public function __call($name, $args) {
		$mode = isset($args[0]) ? $args[0] : GC_CONFIG_MODE_SIMPLE;
		$namespace = isset($args[1]) ? $args[1] : false;
		return $this->get($name, $mode, $namespace);
	}
	//
	// Protected methods.
	/**
	 * This is the actual method that retrieves a configuration file.
	 *
	 * @param string $name JSON configuration file name.
	 * @param string $mode Mechanism to use when loading files.
	 * @return \TooBasic\Config Returns a configuration file.
	 * @throws \TooBasic\ConfigException
	 */
	protected function get($name, $mode, $namespace) {
		//
		// Guessing interpreter class full name @{
		$interpreter = '';
		$className = Names::ConfigClass($name);
		$fullClassName = Names::ClassName("{$namespace}\\{$className}");
		if($namespace !== false && class_exists($fullClassName, true)) {
			$interpreter = $fullClassName;
		} elseif(class_exists($className, true)) {
			$interpreter = $className;
		} else {
			$interpreter = 'TooBasic\\Config';
		}
		// @}
		//
		// Creating an entry for current name.
		if(!isset($this->_configs[$name])) {
			$this->_configs[$name] = [];
		}
		//
		// Creating an entry for the right mode and loading the
		// configuration file.
		if(!isset($this->_configs[$name][$mode])) {
			$this->_configs[$name][$mode] = new $interpreter($name, $mode);
		}

		return $this->_configs[$name][$mode];
	}
}
