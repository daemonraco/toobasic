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
	protected $_cacheParams = array(
		"GET" => array(),
		"POST" => array()
	);
	protected $_errors = array();
	protected $_format = false;
	protected $_lastError = false;
	protected $_lastRun = false;
	protected $_mode = false;
	protected $_name = false;
	protected $_modelsFactory = false;
	protected $_params = array(
		"GET" => array(),
		"POST" => array()
	);
	protected $_requiredParams = array(
		"GET" => array(),
		"POST" => array()
	);
	protected $_status = true;
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

		switch($this->_format) {
			case self::FormatJSON:
				$this->_viewAdapter = new ViewAdapterJSON();
				break;
			case self::FormatBasic:
			default:
				$this->_viewAdapter = new $Defaults["view-adapter"]();
				break;
		}

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
	public function errors() {
		return $this->_errors;
	}
	public function get($key) {
		return isset($this->_assignments[$key]) ? $this->_assignments[$prop] : false;
	}
	public function lastError() {
		return $this->_lastError;
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
		$this->_status = false;

		$this->checkParams();

		$key = $this->cacheKey();
		//
		// Computing cache.
		{
			$prefixComputing = $this->cachePrefix(Exporter::PrefixComputing);

			if($this->_cached) {
				$this->_lastRun = $this->cache->get($prefixComputing, $key);
			} else {
				$this->_lastRun = false;
			}

			if($this->_lastRun && !isset($_REQUEST["debugresetcache"])) {
				$this->_assignments = $this->_lastRun["assignments"];
				$this->_status = $this->_lastRun["status"];
				$this->_errors = $this->_lastRun["errors"];
				$this->_lastError = $this->_lastRun["lasterror"];
			} else {
				$this->autoAssigns();
				$this->_status = $this->dryRun();
				$this->_lastRun = array(
					"status" => $this->_status,
					"assignments" => $this->_assignments,
					"errors" => $this->_errors,
					"lasterror" => $this->_lastError
				);

				if($this->_cached) {
					$this->cache->save($prefixComputing, $key, $this->_lastRun);
				}
			}
		}
		//
		// Render cache.
		{
			$prefixRender = $this->cachePrefix(Exporter::PrefixRender);

			if($this->_cached) {
				$dataBlock = $this->cache->get($prefixRender, $key);
				$this->_lastRun["headers"] = $dataBlock["headers"];
				$this->_lastRun["render"] = $dataBlock["render"];
			} else {
				$this->_lastRun["headers"] = array();
				$this->_lastRun["render"] = false;
			}

			if(!$this->_lastRun["render"] || isset($_REQUEST["debugresetcache"])) {
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun["headers"] = $this->_viewAdapter->headers();
				$this->_lastRun["render"] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_name}.".Paths::ExtensionTemplate));

				if($this->_cached) {
					$this->cache->save($prefixRender, $key, array(
						"headers" => $this->_lastRun["headers"],
						"render" => $this->_lastRun["render"]
					));
				}
			}
		}

		return $this->status();
	}
	public function status() {
		return $this->_status;
	}
	//
	// Protected methods.
	protected function autoAssigns() {
		$this->assign("format", $this->_format);
		$this->assign("mode", $this->_mode);
		$this->assign("name", $this->_name);
	}
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
			foreach($this->_cacheParams as $method => $names) {
				foreach($names as $name) {
					$value = "";
					if(isset($this->_params[$method][$name])) {
						$value = $this->_params[$method][$name];
					}

					$this->_cacheKey.= "_{$name}_{$value}";
				}
			}
		}

		return $this->_cacheKey;
	}
	protected function checkParams() {
		/** @todo */
		/*
		  protected $_requiredParams = array(
		  "GET" => array(),
		  "POST" => array()
		  );
		 */
		$this->loadParams();
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
	protected function loadParams() {
		foreach($_GET as $name => $value) {
			$this->_params["GET"][$name] = $value;
		}
		if($_SERVER["REQUEST_METHOD"] == "POST") {
			foreach($_POST as $name => $value) {
				$this->_params["POST"][$name] = $value;
			}
		}
	}
	protected function setError($code, $message) {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);

		$error = array(
			"code" => $code,
			"message" => $message,
			"location" => array(
				"method" => (isset($callerLine["class"]) ? "{$callerLine["class"]}::" : "")."{$callerLine["function"]}()",
				"file" => $callingLine["file"],
				"line" => $callingLine["line"]
			)
		);

		$this->_errors[] = $error;
		$this->_lastError = $error;

		if($this->_status && $code != HTTPERROR_OK) {
			$this->_status = false;
		}
	}
}
