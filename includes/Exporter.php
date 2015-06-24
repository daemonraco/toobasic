<?php

namespace TooBasic;

/**
 * @class Exporter
 * @abstract
 * 
 * This class is the main specification for any controller in TooBasic.
 */
abstract class Exporter {
	//
	// Constants.
	const PrefixComputing = 'C';
	const PrefixRender = 'R';
	const PrefixService = 'S';
	//
	// Protected class properties.
	/**
	 * @var string[string] Values shared among controllers.
	 */
	protected static $_Shares = array();
	//
	// Protected properties.
	protected $_assignments = array();
	protected $_cached = false;
	protected $_cacheKey = false;
	protected $_cacheParams = array(
		'GET' => array(),
		'POST' => array()
	);
	protected $_errors = array();
	protected $_format = false;
	protected $_lastError = false;
	protected $_lastRun = false;
	protected $_mode = false;
	protected $_name = false;
	protected $_requiredParams = array(
		'GET' => array(),
		'POST' => array()
	);
	protected $_status = true;
	protected $_viewAdapter = false;
	//
	// Magic methods.
	public function __construct($actionName = false) {
		global $Defaults;
		global $ActionName;
		//
		// Checking requested format.
		if(isset($this->params->get->format) && in_array($this->params->get->format, array_keys($Defaults[GC_DEFAULTS_FORMATS]))) {
			$this->_format = $this->params->get->format;
		} else {
			//
			// In case no format was requested or the requested one
			// is wrong, 'basic' is used.
			$this->_format = GC_VIEW_FORMAT_BASIC;
		}
		if(isset($Defaults[GC_DEFAULTS_FORMATS][$this->_format])) {
			$this->_viewAdapter = new $Defaults[GC_DEFAULTS_FORMATS][$this->_format]();
		} else {
			trigger_error("There's no configuration for format '{$this->_format}'", E_USER_ERROR);
		}
		//
		// Checking modes.
		if(isset($this->params->get->mode) && in_array($this->params->get->mode, $Defaults[GC_DEFAULTS_MODES])) {
			$this->_mode = $this->params->get->mode;
		} else {
			//
			// In case no mode was requested or the requested one
			// is wrong, 'action' is used.
			$this->_mode = GC_VIEW_MODE_ACTION;
		}
		//
		// Picking a name for this controller.
		$this->_name = $actionName ? $actionName : $ActionName;
		//
		// Disabling cache if requested.
		if(isset($this->params->debugwithoutcache)) {
			$this->_cached = false;
		}
		//
		// Triggering init method.
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

		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
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
	public function cached() {
		return $this->_cached;
	}
	public function cacheParams() {
		return $this->_cacheParams;
	}
	public function errors() {
		return $this->_errors;
	}
	public function get($key) {
		return isset($this->_assignments[$key]) ? $this->_assignments[$key] : false;
	}
	public function isAssigned($key) {
		return isset($this->_assignments[$key]);
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
	public function requiredParams() {
		return $this->_requiredParams;
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
		global $Defaults;
		global $ActionName;
		global $ServiceName;
		global $LayoutName;

		$this->assign('action', $ActionName);
		$this->assign('service', $ServiceName);
		$this->assign('layout', $LayoutName);
		$this->assign('name', $this->_name);
		$this->assign('lang', $Defaults[GC_DEFAULTS_LANGS_DEFAULTLANG]);
	}
	protected function cacheKey() {
		if($this->_cacheKey === false) {
			$this->_cacheKey = get_called_class();
			foreach($this->_cacheParams as $method => $names) {
				foreach($names as $name) {
					$value = $this->params->{$method}->{$name};
					$this->_cacheKey.= "_{$name}_{$value}";
				}
			}
		}

		return $this->_cacheKey;
	}
	protected function checkParams() {
		foreach($this->_requiredParams as $method => $params) {
			if($method == 'GET' || $method == $_SERVER['REQUEST_METHOD']) {
				foreach($params as $param) {
					if(!isset($this->params->{$method}->{$param})) {
						$this->setError(HTTPERROR_BAD_REQUEST, "Parameter '{$param}' is not set (".strtoupper($method).')');
					}
				}
			}
		}
	}
	protected function cachePrefix($extra = '') {
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
		$method = Params::Instance()->server->REQUEST_METHOD;
		//
		// If there's a specific method for the current request method,
		// it is run, otherwise the basic run method is called
		if(method_exists($this, "run{$method}")) {
			$out = $this->{"run{$method}"}();
		} elseif(method_exists($this, 'basicRun')) {
			$out = $this->basicRun();
		} else {
			$this->setError(HTTPERROR_NOT_IMPLEMENTED, "Request method '{$method}' is not implemented");
		}

		return $out;
	}
	protected function init() {
		if($this->params->hasDebugs()) {
			$this->_cached = false;
		}
		//
		// This is to avoid cache collision when a controller is
		// requested with different modes.
		$this->_cacheParams['GET'][] = 'mode';
	}
	protected function setError($code, $message) {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);

		$error = array(
			'code' => $code,
			'message' => $message,
			'location' => array(
				'method' => (isset($callerLine['class']) ? "{$callerLine['class']}::" : '')."{$callerLine['function']}()",
				'file' => $callingLine['file'],
				'line' => $callingLine['line']
			)
		);

		$this->_errors[] = $error;
		$this->_lastError = $error;

		if($this->_status && $code != HTTPERROR_OK) {
			$this->_status = false;
		}
	}
	//
	// Protected class methods.
	protected static function GetShare($key) {
		return isset(self::$_Shares[$key]) ? self::$_Shares[$key] : null;
	}
	protected static function Share($key, $value) {
		self::$_Shares[$key] = $value;

		return self::GetShare($key);
	}
}
