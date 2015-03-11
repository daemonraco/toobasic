<?php

/**
 * @abstract
 */
abstract class Exporter {
	//
	// Constants
	const FormatBasic = "basic";
	const FormatJSON = "json";
	const ModeAction = "action";
	const ModeModal = "modal";
	const PrefixComputing = "C";
	const PrefixRender = "R";
	//
	// Protected class properties
	protected static $_Cache = false;
	//
	// Protected properties
	protected $_assignments = array();
	protected $_cached = false;
	protected $_cacheKey = false;
	protected $_cacheParams = array();
	protected $_format = false;
	protected $_lastRun = false;
	protected $_mode = false;
	protected $_name = false;
	protected $_modelsFactory = false;
	protected $_params = array();
	protected $_requiredParams = array(
		"GET" => array()
	);
	protected $_viewAdapter = false;
	//
	// Magic methods.
	public function __construct() {
		$this->_modelsFactory = ModelsFactory::Instance();

		global $Defaults;

		if(isset($_REQUEST["format"]) && in_array($_REQUEST["format"], array(self::FormatBasic, self::FormatJSON))) {
			$this->_format = $_REQUEST["format"];
		} else {
			$this->_format = self::FormatBasic;
		}

		if(isset($_REQUEST["mode"]) && in_array($_REQUEST["mode"], array(self::ModeAction, self::ModeModal))) {
			$this->_mode = $_REQUEST["mode"];
		} else {
			$this->_mode = self::ModeAction;
		}

		$this->_name = isset($_REQUEST["action"]) ? $_REQUEST["action"] : $Defaults["action"];

		$this->_viewAdapter = new $Defaults["view-adapter"]();

		if(isset($_REQUEST["debugwithoutcache"])) {
			$this->_cached = false;
		}

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
	public function assignments() {
		return $this->_assignments;
	}
	public function get($key) {
		return isset($this->_assignments[$key]) ? $this->_assignments[$prop] : false;
	}
	public function lastRun() {
		return $this->_lastRun;
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

		$key = $this->cacheKey();
		//
		// Computing cache.
		{
			$prefix = $this->cachePrefix(Exporter::PrefixComputing);

			if($this->_cached) {
				$this->_lastRun = $this->cache->get($prefix, $key);
			} else {
				$this->_lastRun = false;
			}

			if($this->_lastRun && !isset($_REQUEST["debugresetcache"])) {
				$this->_assignments = $this->_lastRun["assignments"];
				$out = $this->_lastRun["result"];
			} else {
				$out = $this->dryRun();
				$this->_lastRun = array(
					"result" => $out,
					"assignments" => $this->_assignments
				);

				if($this->_cached) {
					$this->cache->save($prefix, $key, $this->_lastRun);
				}
			}
		}
		//
		// Render cache.
		{
			$prefix = $this->cachePrefix(Exporter::PrefixRender);

			if($this->_cached) {
				$this->_lastRun["render"] = $this->cache->get($prefix, $key);
			} else {
				$this->_lastRun["render"] = false;
			}

			if(!$this->_lastRun["render"] || isset($_REQUEST["debugresetcache"])) {
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun["render"] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_name}.".Paths::ExtensionTemplate));

				if($this->_cached) {
					$this->cache->save($prefix, $key, $this->_lastRun["render"]);
				}
			}
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
			$prefix = $this->cachePrefix(Exporter::PrefixComputing);

			if($this->_cached) {
				$this->_lastRun = $this->cache->get($prefix, $key);
			} else {
				$this->_lastRun = false;
			}

			if($this->_lastRun) {
				$this->_assignments = $this->_lastRun["assignments"];
				$out = $this->_lastRun["result"];
			} else {
				$out = $this->dryRun();
				$this->_lastRun = array(
					"result" => $out,
					"assignments" => $this->_assignments
				);

				if($this->_cached) {
					$this->cache->save($prefix, $key, $this->_lastRun);
				}
			}
		}
		//
		// Render cache.
		{
			$prefix = $this->cachePrefix(Exporter::PrefixRender);

			$this->_lastRun["render"] = $this->cache->get($prefix, $key);
			if(!$out) {
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun["render"] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_name}.".Paths::ExtensionTemplate));

				if($this->_cached) {
					$this->cache->save($prefix, $key, $this->_lastRun["render"]);
				}
			}
		}

		return $out;
	}
	protected function checkParams() {
		
	}
	protected function cachePrefix($extra = "") {
		if($extra == self::PrefixRender) {
			$extra = "{$extra}_".strtoupper($this->_format);
		}

		return get_called_class()."_{$extra}";
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
