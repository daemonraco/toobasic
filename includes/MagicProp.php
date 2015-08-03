<?php

/**
 * @file MagicGets.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class MagicPropException
 * This exeption is thrown whenever 'MagicProp' founds a halting error.
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
 * 	- \TooBasic\ItemsFactoryProvider
 * 	- \TooBasic\Translate
 * This can also be integrated inside a magic method '__get()' to provide access
 * to these singletons in a pretty and easy way.
 */
class MagicProp extends Singleton {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\CacheAdatper Current cache adapter's singleton.
	 */
	protected $_cache = false;
	/**
	 * @var \TooBasic\ModelsFactory Models factory singleton.
	 */
	protected $_modelsFactory = false;
	/**
	 * @var \TooBasic\Params Parameters manager singleton.
	 */
	protected $_params = false;
	/**
	 * @var \TooBasic\Paths Paths manager singleton.
	 */
	protected $_paths = false;
	/**
	 * @var \TooBasic\ItemsFactoryProvider Item representations factory
	 * singleton.
	 */
	protected $_representations = false;
	/**
	 * @var \TooBasic\Translate Translations manager singleton.
	 */
	protected $_translate = false;
	//
	// Magic methods.
	/**
	 * This method provides a easy way to access each singleton:
	 * 	- 'model' for \TooBasic\ModelsFactory
	 * 	- 'representation' for \TooBasic\ItemsFactoryProvider
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
		// Looking for the requested property.
		if($prop == 'model') {
			$out = $this->modelsFactory();
		} elseif($prop == 'representation') {
			$out = $this->representations();
		} elseif($prop == 'tr' || $prop == 'translate') {
			$out = $this->translate();
		} elseif($prop == 'params') {
			$out = $this->params();
		} elseif($prop == 'cache') {
			$out = $this->cache();
		} elseif($prop == 'paths') {
			$out = $this->paths();
		} else {
			//
			// If it was not found an exception is thrown.
			throw new MagicPropException("Unhandled propoerty '{$prop}'");
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
			$this->_cache = Adapter::Factory($Defaults[GC_DEFAULTS_CACHE_ADAPTER]);
		}

		return $this->_cache;
	}
	/**
	 * This method return a models factory singleton. If it's not yet
	 * contructed it creates it.
	 *
	 * @return \TooBasic\ModelsFactory Returns a factory.
	 */
	protected function modelsFactory() {
		if($this->_modelsFactory === false) {
			$this->_modelsFactory = ModelsFactory::Instance();
		}

		return $this->_modelsFactory;
	}
	/**
	 * This method return a parameters manager singleton. If it's not yet
	 * contructed it creates it.
	 *
	 * @return \TooBasic\Translate Returns a manager singleton.
	 */
	protected function params() {
		if($this->_params === false) {
			$this->_params = Params::Instance();
		}

		return $this->_params;
	}
	/**
	 * This method return a paths manager singleton. If it's not yet
	 * contructed it creates it.
	 *
	 * @return \TooBasic\Translate Returns a manager singleton.
	 */
	protected function paths() {
		if($this->_paths === false) {
			$this->_paths = Paths::Instance();
		}

		return $this->_paths;
	}
	/**
	 * This method return a item representations factory singleton. If it's
	 * not yet contructed it creates it.
	 *
	 * @return \TooBasic\ModelsFactory Returns a factory.
	 */
	protected function representations() {
		if($this->_representations === false) {
			$this->_representations = ItemsFactoryProvider::Instance();
		}

		return $this->_representations;
	}
	/**
	 * This method return a translations manager singleton. If it's not yet
	 * contructed it creates it.
	 *
	 * @return \TooBasic\Translate Returns a manager singleton.
	 */
	protected function translate() {
		if($this->_translate === false) {
			$this->_translate = Translate::Instance();
		}

		return $this->_translate;
	}
}
