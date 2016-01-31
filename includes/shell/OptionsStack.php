<?php

/**
 * @file OptionsStack
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Shell\Option;
use TooBasic\Shell\Options;

/**
 * @class OptionsStack
 * This class is an addapter for shell options that can be used by 'Params'.
 */
class OptionsStack extends \TooBasic\ParamsStack {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Shell\Options Options singleton shortcut.
	 */
	protected $_options = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string[string] $list Parameter given for compatibility.
	 */
	public function __construct($list) {
		//
		// Singleton shortcut.
		$this->_options = Options::Instance();
	}
	/**
	 * This magic method provides a easy way to access an option's value.
	 *
	 * @param string $name Option name.
	 * @return string Found value or null when it's not present in shell
	 * command line.
	 */
	public function __get($name) {
		//
		// Default values.
		$out = null;
		//
		// Searching and fetching option.
		$opt = $this->_options->option($name);
		//
		// Checking option existence.
		if($opt) {
			//
			// Checking if it is an option with or without value.
			if($opt->type() == Option::TypeNoValue) {
				$out = $opt->activated();
			} else {
				//
				// If it has a value it will try to return it only
				// when active.
				if($opt->activated()) {
					$out = $opt->value();
				}
			}
		}
		//
		// Returning what was found.
		return $out;
	}
	/**
	 * This magic method provides a way to know if certain option was given.
	 *
	 * @param string $name Otion's name to look for.
	 * @return boolean Returns true when the option exists and is activated.
	 */
	public function __isset($name) {
		//
		// Searching and fetching option.
		$opt = $this->_options->option($name);
		//
		// Checking option existence and status.
		return $opt && $opt->activated();
	}
	/**
	 * This method should not be used.
	 */
	public function __set($key, $value) {
		throw new Exception("Options given in a command line can't be changed.");
	}
	//
	// Public properties.
	/**
	 * This method should not be used.
	 */
	public function addValue($key, $value) {
		throw new Exception("Options given in a command line can't be changed.");
	}
	/**
	 * This method should not be used.
	 */
	public function addValues($values) {
		throw new Exception("Options given in a command line can't be changed.");
	}
	/**
	 * This method allows access to all active options values.
	 *
	 * @return string[string] Associative list of option values.
	 */
	public function all() {
		//
		// Default values.
		$out = array();
		//
		// Checking each active option.
		foreach($this->_options->activeOptions() as $optName) {
			$opt = $this->_options->option($optName);
			//
			// Getting the proper value.
			$out[$opt->name()] = $opt->type() == Option::TypeNoValue ? true : $opt->value();
		}

		return $out;
	}
	/**
	 * This method should not be used.
	 */
	public function debugs() {
		return array();
	}
	/**
	 * This method should not be used.
	 */
	public function hasDebugs() {
		return false;
	}
}
