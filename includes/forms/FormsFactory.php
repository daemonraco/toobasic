<?php

/**
 * @file FormsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class FormsFactory
 * This singleton class provides an easy access to any form.
 */
class FormsFactory extends \TooBasic\Singleton {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Forms\Form[string] List of already loaded forms.
	 */
	protected $_forms = array();
	//
	// Magic methods.
	/**
	 * This method is an alias for 'get()'.
	 *
	 * @param string $name Name of the form to be loaded and returned.
	 * @return \TooBasic\Forms\Form Returns a loaded form.
	 * @throws \TooBasic\Forms\FormsException
	 */
	public function __get($name) {
		return $this->get($name);
	}
	//
	// Public methods.
	/**
	 * This method loads a form configuration and return it as an object that
	 * can be used for further functionalities.
	 *
	 * @param string $name Name of the form to be loaded and returned.
	 * @return \TooBasic\Forms\Form Returns a loaded form.
	 * @throws \TooBasic\Forms\FormsException
	 */
	public function get($name) {
		//
		// Default values.
		$out = false;
		//
		// Checking if the requested form was already loaded.
		if(isset($this->_forms[$name])) {
			$out = $this->_forms[$name];
		} else {
			//
			// Creating an object to manage the request form.
			$this->_forms[$name] = new Form($name);
			//
			// Checking loaded form status.
			if($this->_forms[$name]->status()) {
				//
				// Storing its pointer for future calls.
				$out = $this->_forms[$name];
			} else {
				throw new FormsException("Unable to load form '{$name}'.");
			}
		}

		return $out;
	}
}
