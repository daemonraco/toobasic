<?php

namespace TooBasic;

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
	const PrefixService = "S";
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
	protected $_params = null;
	protected $_requiredParams = array(
		"GET" => array(),
		"POST" => array()
	);
	protected $_status = true;
	protected $_translate = null;
	protected $_viewAdapter = false;
	//
	// Magic methods.
	public function __construct($actionName = false) {
		$this->_modelsFactory = ModelsFactory::Instance();
		$this->_translate = Translate::Instance();

		global $Defaults;
		global $ActionName;

		$this->_params = Params::Instance();

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

		$this->_name = $actionName ? $actionName : $ActionName;

		switch($this->_format) {
			case self::FormatJSON:
				$this->_viewAdapter = new ViewAdapterJSON();
				break;
			case self::FormatBasic:
			default:
				$this->_viewAdapter = new $Defaults["view-adapter"]();
				break;
		}

		if(isset($this->_params->debugwithoutcache)) {
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
		} elseif($prop == "translate") {
			$out = $this->_translate;
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
	public function massiveAssign($list) {
		$this->_assignments = array_merge($this->assignments(), $list);
		return $this->assignments();
	}
	public function resetCache() {
		$this->cache->delete($this->cacheKey());
	}
	/**
	 * @abstract
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	abstract public function run();
	public function status() {
		return $this->_status;
	}
	//
	// Protected methods.
	protected function autoAssigns() {
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
					$value = $this->_params->{$method}->{$name};
					$this->_cacheKey.= "_{$name}_{$value}";
				}
			}
		}

		return $this->_cacheKey;
	}
	protected function checkParams() {
		foreach($this->_requiredParams as $method => $params) {
			foreach($params as $param) {
				if(!isset($this->_params->{$method}->{$param})) {
					$this->setError(HTTPERROR_BAD_REQUEST, "Parameter '{$param}' is not set (".strtoupper($method).")");
				}
			}
		}
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
		if($this->_params->hasDebugs()) {
			$this->_cached = false;
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
