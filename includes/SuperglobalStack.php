<?php

/**
 * @file SuperglobalStack.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class SuperglobalStack
 * This class represents a simple list of values associated to a key and it's
 * mainly used by \TooBasic\Params.
 */
class SuperglobalStack extends ParamsStack {
	//
	// Protected properties.
	/**
	 * @var string Name of the superglobal being affected.
	 */
	protected $_paramName = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string[string] $list Initial list on which start this stack.
	 */
	public function __construct($listName) {
		//
		// Saving global name.
		$this->_paramName = $listName;
		//
		// Global dependencies.
		global ${$this->_paramName};
		//
		// Load current values.
		$this->_params = ${$this->_paramName};
	}
	/**
	 * This magic method provides a easy way to set values.
	 *
	 * @param string $key Name to associate with the new value.
	 * @param string $value Value to add/set.
	 * @return string Found value or null when it's not present.
	 */
	public function __unset($key) {
		return $this->removeValue($key);
	}
	//
	// Public properties.
	/**
	 * This method allows to intentionaly set a value on this params stack. It
	 * will affect the real super global.
	 *
	 * @param string $key Name to associate with the new value.
	 * @param string $value Value to add/set.
	 */
	public function addValue($key, $value) {
		//
		// If the key is numerical, it is a mistake and therefore ignored.
		if(!is_numeric($key)) {
			//
			// Global dependencies.
			global ${$this->_paramName};
			//
			// Changing super global.
			${$this->_paramName}[$key] = $value;
			//
			// Setting internal values.
			$this->_params[$key] = $value;
		}

		return $value;
	}
	public function removeValue($key) {
		//
		// Default values.
		$isset = false;
		//
		// If the key is numerical, it is a mistake and therefore ignored.
		if(!is_numeric($key)) {
			//
			// Global dependencies.
			global ${$this->_paramName};
			//
			// Changing super global.
			unset(${$this->_paramName}[$key]);
			//
			// Removing from internal values.
			unset($this->_params[$key]);

			$isset = isset($this->_params[$key]);
		}

		return $isset;
	}
}
