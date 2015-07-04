<?php

/**
 * @file Model.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class Model
 * This class represent a generic model to manage information and store complex
 * behaviors on the site.
 *
 * Each model can be used in two ways, as a singleton or as a common object:
 * 	- When it is flagged as singleton, the factory \TooBasic\ModelsFactory
 * 	  will create an instance in the first call for construction and then
 * 	  return the same instance for the rest of the calls.
 * 	- When it's not flagged as singleton the factory will always create a new
 * 	  instance for each construction call.
 */
abstract class Model {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\ModelsFactory Internal pointer to it's construction
	 * factory.
	 */
	protected $_modelsFactory = false;
	/**
	 * @var boolean This flag indicates if this model acts as a singleton or
	 * not.
	 */
	protected $_isSingleton = true;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->_modelsFactory = ModelsFactory::Instance();
		$this->init();
	}
	/**
	 * This method extends this class behavior through 'MagicProp'.
	 *
	 * @param string $prop Property to look for.
	 * @return mixed Property found or false when it's not.
	 */
	public function __get($prop) {
		//
		// Default values.
		$out = false;
		//
		// Safeguarding 'MagicProp' exceptions.
		try {
			//
			// Forwarding the property request.
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			
		}
		//
		// Returning what was found.
		return $out;
	}
	//
	// Public methods.
	/**
	 * Provides access to the singleton flag.
	 *
	 * @return boolen True when it's flagged as singleton.
	 */
	public function isSingleton() {
		return $this->_isSingleton;
	}
	//
	// Protected methods.
	/**
	 * @abstract
	 * Every model must implement a way to initialize it self.
	 */
	abstract protected function init();
}
