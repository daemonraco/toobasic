<?php

/**
 * @abstract
 */
abstract class Exporter {
	//
	// Constants
	const ComputingPrefix = "C";
	//
	// Protected class properties
	protected static $_Cache = false;
	//
	// Protected properties
	protected $_assignments = array();
	protected $_cached = false;
	protected $_cacheKey = false;
	protected $_cachePrefix = false;
	protected $_cacheParams = array();
	protected $_modelsFactory = false;
	protected $_params = array();
	protected $_requiredParams = array(
		"GET" => array()
	);
	//
	// Magic methods.
	public function __construct() {
		$this->_modelsFactory = ModelsFactory::Instance();
		$this->init();
	}
	/**
	 * @todo doc
	 *
	 * @param type $prop @todo doc
	 * @return mixed @todo doc
	 */
	public function __get($prop) {
		$out = false;

		if($prop == "model") {
			$out = $this->_modelsFactory;
		} elseif($prop == "cache") {
			if(self::$_Cache === false) {
				global $Defaults;
				self::$_Cache = Adapter::Factory($Defaults["cache-adapter"]);
			}
			$out = self::$_Cache;
		} else {
			$out = $this->get($prop);
		}

		return $out;
	}
	//
	// Public methods.
	public function assign($key, $data) {
		$this->_assignments[$key] = $data;
	}
	public function get($key) {
		return isset($this->_assignments[$key]) ? $this->_assignments[$prop] : false;
	}
	public function resetCache() {
		$this->cache->delete($this->cacheKey());
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		$out = false;

		$this->checkParams();

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
	protected function cacheKey() {
		if($this->_cacheKey === false) {
			$this->_cacheKey = get_called_class();
			foreach($this->_cacheParams as $name) {
				$this->_cacheKey.= "_{$name}_".(isset($this->_params[$name]) ? $this->_params[$name] : "");
			}
		}

		return $this->_cacheKey;
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	protected function cachedRun() {
		$out = false;

		$key = $this->cacheKey();
		//
		// Computing cache.
		{
			$prefix = $this->cachePrefix(Exporter::ComputingPrefix);

			$dataBlock = $this->cache->get($prefix, $key);
			if($dataBlock) {
				$this->_assignments = $dataBlock["assignments"];
				$out = $dataBlock["result"];
			} else {
				$out = $this->dryRun();
				$dataBlock = $this->cache->save($prefix, $key, array(
					"result" => $out,
					"assignments" => $this->_assignments
				));
			}
		}

		return $out;
	}
	protected function checkParams() {
		
	}
	protected function cachePrefix($extra = "") {
		if($this->_cachePrefix === false) {
			$this->_cachePrefix = get_called_class()."_{$extra}";
		}

		return $this->_cachePrefix;
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
