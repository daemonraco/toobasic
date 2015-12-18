<?php

/**
 * @file ParamsStack.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ParamsStack
 * This class represents a simple list of values associated to a key and it's
 * mainly used by \TooBasic\Params.
 */
class ParamsStack {
	//
	// Protected properties.
	/**
	 * @var string[] List of detected debug parameters.
	 */
	protected $_debugs = false;
	/**
	 * @var boolean This flag inidicates that a debug parameter has been
	 * detected.
	 */
	protected $_hasDebugs = false;
	/**
	 * @var string[string] List of values held by this stack.
	 */
	protected $_params = array();
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string[string] $list Initial list on which start this stack.
	 */
	public function __construct($list) {
		$this->_params = $list;
	}
	/**
	 * This magic method provides a easy way to access values through their
	 * keys.
	 *
	 * @param string $name Key to look for.
	 * @return string Found value or null when it's not present.
	 */
	public function __get($name) {
		//
		// Default values.
		$out = null;
		//
		// Searching and fetching.
		if(isset($this->{$name})) {
			$out = $this->_params[$name];
		}
		//
		// Returning what was found.
		return $out;
	}
	/**
	 * This magic method provides a way to know if certain key is present.
	 *
	 * @param string $name Key to look for.
	 * @return boolean Returns true when it's present.
	 */
	public function __isset($name) {
		$out = false;
		//
		// Global dependencies.
		global $Defaults;
		//
		// Avoiding debug parameters when they are disabled.
		if(!$Defaults[GC_DEFAULTS_DISABLED_DEBUGS] || !preg_match('/^debug([a-z0-9]*)$/', $name)) {
			$out = isset($this->_params[$name]);
		}

		return $out;
	}
	/**
	 * This magic method provides a easy way to set values.
	 *
	 * @param string $key Name to associate with the new value.
	 * @param string $value Value to add/set.
	 * @return string Found value or null when it's not present.
	 */
	public function __set($key, $value) {
		return $this->addValue($key, $value);
	}
	//
	// Public properties.
	/**
	 * This method allows to intentionaly set a value on this params stack. It
	 * won't affect the real super global.
	 *
	 * @param string $key Name to associate with the new value.
	 * @param string $value Value to add/set.
	 */
	public function addValue($key, $value) {
		//
		// If the key is numerical, it is a mistake and therefore ignored.
		if(!is_numeric($key)) {
			$this->_params[$key] = $value;
			$value = null;
		}

		return $value;
	}
	/**
	 * This method allows to intentionaly set values on this params stack. It
	 * won't affect the real super global.
	 *
	 * @param string[string] $values Associative list of values to set. It
	 * overrides existing values on identical keys.
	 */
	public function addValues($values) {
		//
		// Checking and adding each given value.
		foreach($values as $key => $value) {
			$this->addValue($key, $value);
		}
	}
	/**
	 * This method allows to access a complete list of values on this params
	 * stack.
	 *
	 * @return string[string] Associative list of stored values.
	 */
	public function all() {
		return $this->_params;
	}
	/**
	 * This method returns the list of found debug parameters.
	 *
	 * @return string[] Returns a list of found debug parameters.
	 */
	public function debugs() {
		//
		// Checking if it's already calculated.
		if($this->_debugs === false) {
			//
			// Global dependencies.
			global $Defaults;
			//
			// Default values.
			$this->_debugs = array();
			//
			// Avoiding analisis when debugs are disabled.
			if(!$Defaults[GC_DEFAULTS_DISABLED_DEBUGS]) {
				//
				// Detecting debug parameters
				foreach($this->_params as $param => $value) {
					if(preg_match('/^debug([a-z0-9]*)$/', $param)) {
						//
						// At this point, we do have
						// debugs.
						$this->_hasDebugs = true;
						//
						// Adding debugs to the list.
						$this->_debugs[$param] = $value;
					}
				}
			}
		}
		//
		// Returning what was found.
		return $this->_debugs;
	}
	/**
	 * This method allows to know if a debug parameter was given.
	 *
	 * @return boolean Returns true when at least one debug parameter was
	 * given.
	 */
	public function hasDebugs() {
		//
		// Enforcing debugs check and listing.
		$this->debugs();
		return $this->_hasDebugs;
	}
}
