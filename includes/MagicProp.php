<?php

/**
 * @file MagicGets.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class MagicPropException
 * This exeption is thrown whenever 'MagicProp' finds a halting error.
 */
class MagicPropException extends Exception {
	
}

/**
 * @class MagicProp
 * This class is some kind of facade for a set of important singletons and they
 * are:
 * 	- \TooBasic\CacheAdatper (the current one)
 * 	- \TooBasic\ModelsFactory
 * 	- \TooBasic\Params
 * 	- \TooBasic\Paths
 * 	- \TooBasic\Representations\ItemsFactoryProvider
 * 	- \TooBasic\Translate
 * This can also be integrated inside a magic method '__get()' to provide access
 * to these singletons in a pretty and easy way.
 */
class MagicProp extends Singleton {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Singleton[string] List of loaded properties.
	 */
	protected $_properties = array();
	/**
	 * @var \TooBasic\CacheAdatper Current cache adapter's singleton.
	 */
	protected $_cache = false;
	//
	// Magic methods.
	/**
	 * This method provides a easy way to access each singleton:
	 * 	- 'model' for \TooBasic\ModelsFactory
	 * 	- 'representation' for \TooBasic\Representations\ItemsFactoryProvider
	 * 	- 'translate' for \TooBasic\Translate
	 * 	- 'params' for \TooBasic\Params
	 * 	- 'cache' for \TooBasic\CacheAdatper
	 * 	- 'paths' for \TooBasic\Paths
	 * When an unknown property is given an exception of type
	 * MagicPropException is thrown.
	 *
	 * @param stirng $prop Property to look for.
	 * @return mixed Returns the requested property.
	 * @throws MagicPropException
	 */
	public function __get($prop) {
		//
		// Default values.
		$out = null;
		//
		// Solving alias names.
		$cleanProp = $this->solveAliases($prop);
		//
		// Looking for the requested property.
		$out = $this->getProperty($cleanProp);
		//
		// Checking the loaded property.
		if($out === null) {
			//
			// Loading not so dynamic properties.
			if($cleanProp == GC_MAGICPROP_PROP_CACHE) {
				$out = $this->cache();
			}
		}
		//
		// Checking that something was found, otherwise it's an error.
		if($out === null) {
			//
			// If it was not found an exception is thrown.
			$message = "Unhandled property '{$cleanProp}'";
			if($prop != $cleanProp) {
				$message.= " given as '{$prop}'";
			}
			throw new MagicPropException($message);
		}
		//
		// Returning the found singleton.
		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method return the current cache adapter's singleton. If it's not
	 * yet contructed it creates it.
	 *
	 * @return \TooBasic\CacheAdatper Returns a cache adapter singleton.
	 */
	protected function cache() {
		if($this->_cache === false) {
			global $Defaults;
			$this->_cache = \TooBasic\Adapters\Adapter::Factory($Defaults[GC_DEFAULTS_CACHE_ADAPTER]);
		}

		return $this->_cache;
	}
	/**
	 * This method takes a property name and returns the proper singleton.
	 *
	 * @param string $prop Property name.
	 * @return \TooBasic\Singleton Returns the requested property or NULL when
	 * not found.
	 */
	protected function getProperty($prop) {
		//
		// Default values.
		$out = null;
		//
		// Global dependecies.
		global $MagicProps;
		//
		// Is it a known property?
		if(isset($MagicProps[GC_MAGICPROP_PROPERTIES][$prop])) {
			//
			// Was it already loaded?
			if(!isset($this->_properties[$prop])) {
				$aux = $MagicProps[GC_MAGICPROP_PROPERTIES][$prop];
				$this->_properties[$prop] = $aux::Instance();
			}
			//
			// Setting the return property.
			$out = $this->_properties[$prop];
		}

		return $out;
	}
	/**
	 * This method takes a property name and if it's an alias name, it tries
	 * to change it into a non-alias property name.
	 *
	 * @param string $prop Property name.
	 * @return string Returns a property name.
	 */
	protected function solveAliases($prop) {
		//
		// Default values.
		$out = $prop;
		//
		// Global dependencies.
		global $MagicProps;
		//
		// Swapping names until it's not an alias.
		$done = false;
		while(!$done) {
			//
			// If it's not an alias it's assumed to be found. If it's
			// an alias it's value is used for the next try.
			// Otherwise, it's left with it's last value and then
			// returned.
			if(isset($MagicProps[GC_MAGICPROP_PROPERTIES][$out])) {
				$done = true;
			} elseif(isset($MagicProps[GC_MAGICPROP_ALIASES][$out])) {
				$out = $MagicProps[GC_MAGICPROP_ALIASES][$out];
			} else {
				//
				// Not found.
				$done = true;
			}
		}

		return $out;
	}
}
