<?php

namespace TooBasic\Forms;

class FormsFactory extends \TooBasic\Singleton {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Forms\Form[string] List of already loaded forms.
	 */
	protected $_forms = array();
	//
	// Magic methods.
	public function __get($name) {
		return $this->get($name);
	}
	//
	// Public methods.
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
