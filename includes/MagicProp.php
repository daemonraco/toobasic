<?php

/**
 * @file MagicGets.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

class MagicPropException extends \Exception {
	
}

class MagicProp extends Singleton {
	//
	// Protected properties.
	protected $_cache = false;
	protected $_modelsFactory = false;
	protected $_params = false;
	protected $_paths = false;
	protected $_representations = false;
	protected $_translate = false;
	//
	// Magic methods.
	public function __construct() {
		
	}
	public function __get($prop) {
		$out = null;

		if($prop == "model") {
			$out = $this->modelsFactory();
		} elseif($prop == "representation") {
			$out = $this->representations();
		} elseif($prop == "translate") {
			$out = $this->translate();
		} elseif($prop == "params") {
			$out = $this->params();
		} elseif($prop == "cache") {
			$out = $this->cache();
		} elseif($prop == "paths") {
			$out = $this->paths();
		} else {
			throw new MagicPropException("Unhandled propoerty '{$prop}'");
		}

		return $out;
	}
	//
	// Protected methods.
	protected function cache() {
		if($this->_cache === false) {
			global $Defaults;
			$this->_cache = Adapter::Factory($Defaults["cache-adapter"]);
		}

		return $this->_cache;
	}
	protected function modelsFactory() {
		if($this->_modelsFactory === false) {
			$this->_modelsFactory = ModelsFactory::Instance();
		}

		return $this->_modelsFactory;
	}
	protected function params() {
		if($this->_params === false) {
			$this->_params = Params::Instance();
		}

		return $this->_params;
	}
	protected function paths() {
		if($this->_paths === false) {
			$this->_paths = Paths::Instance();
		}

		return $this->_paths;
	}
	protected function representations() {
		if($this->_representations === false) {
			$this->_representations = ItemsFactoryProvider::Instance();
		}

		return $this->_representations;
	}
	protected function translate() {
		if($this->_translate === false) {
			$this->_translate = Translate::Instance();
		}

		return $this->_translate;
	}
}
