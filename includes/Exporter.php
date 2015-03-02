<?php

abstract class Exporter {
	//
	// Protected class properties
	//
	// Protected properties
	protected $_cached = false;
	protected $_ModelsFactory = false;
	//
	// Magic methods.
	public function __construct() {
		$this->_ModelsFactory = ModelsFactory::Instance();
		$this->init();
	}
	/**
	 *  @todo doc
	 *
	 * @param type $prop @todo doc
	 * @return mixed @todo doc
	 */
	public function __get($prop) {
		$out = false;

		if($prop == "model") {
			$out = $this->_ModelsFactory;
		}

		return $out;
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		$out = false;

		if($this->_cached) {
			$out = $this->cachedRun();
		} else {
			$out = $this->dryRun();
		}

		return $out;
	}
	//
	// Protected methods.
	/**
	 * @abstract
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	abstract protected function basicRun();
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	protected function cachedRun() {
		$out = false;

		return $out;
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	protected function dryRun() {
		$out = false;
		//
		// Detecting current request method.
		$method = $_SERVER["REQUEST_METHOD"];
		//
		// If there's a specific method for the current request method,
		// it is run, otherwise the basic run method is called
		if(method_exists($this, "run{$method}")) {
			$out = $this->{"run{$method}"}();
		} else {
			$out = $this->basicRun();
		}

		return $out;
	}
	protected function init() {
		
	}
}
