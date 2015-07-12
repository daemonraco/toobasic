<?php

/**
 * @file Exporter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Exporter
 * @abstract
 * 
 * This class is the main specification for any controller in TooBasic.
 */
abstract class Exporter extends AbstractExporter {
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
	/**
	 * @var string Indicates how long a cache entry lives. TRUE means 'large'
	 * and FALSE means no cache for this class.
	 */
	protected $_cached = false;
	/**
	 * @var string Current key being used to identify this instance cache
	 * entry.
	 */
	protected $_cacheKey = false;
	/**
	 * @var string[string] Lists of parameters used to generate a cache entry
	 * key.
	 */
	protected $_cacheParams = array(
		'GET' => array(),
		'POST' => array()
	);
	/**
	 * @var string Format in which this controller/service is being displayed.
	 */
	protected $_format = false;
	/**
	 * @var string Mode in which this controller/service is being displayed.
	 */
	protected $_mode = false;
	/**
	 * @var string Current controller/service name.
	 */
	protected $_name = false;
	/**
	 * @var string[string][] List of required parameters grouped by request
	 * method.
	 */
	protected $_requiredParams = array(
		'GET' => array(),
		'POST' => array()
	);
	/**
	 * @var \TooBasic\ViewAdapter Pointer to the current view rendering
	 * adapter.
	 */
	protected $_viewAdapter = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $actionName internal identifier for the current
	 * action/service being represented.
	 */
	public function __construct($actionName = false) {
		parent::__construct($actionName);
		//
		// Global dependencies.
		global $Defaults;
		global $ActionName;
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
	//
	// Public methods.
	/**
	 * Allows to know how long a cache entry of this controller/service lasts.
	 *
	 * @return string Current cache size.
	 */
	public function cached() {
		return $this->_cached;
	}
	/**
	 * Provides access to which parameters are being used on cache key
	 * generations.
	 *
	 * @return string[string][] Returns a list of parameters grouped by
	 * request method.
	 */
	public function cacheParams() {
		return $this->_cacheParams;
	}
	/**
	 * Provides access to the list of required parameters.
	 *
	 * @return string[string][] List of parameters grouped by request method.
	 */
	public function requiredParams() {
		return $this->_requiredParams;
	}
	/**
	 * Removes the cache entry for this controller/service.
	 */
	public function resetCache() {
		$this->cache->delete($this->cacheKey());
	}
	//
	// Protected methods.
	/**
	 * This method generates a list of default assignments useful for any
	 * controller/service.
	 */
	protected function autoAssigns() {
		parent::autoAssigns();
		//
		// Global dependencies.
		global $Defaults;
		global $ActionName;
		global $ServiceName;
		global $LayoutName;
		//
		// Current action name (if any).
		$this->assign('action', $ActionName);
		//
		// Current service name (if any).
		$this->assign('service', $ServiceName);
		//
		// Current layout name (if any).
		$this->assign('layout', $LayoutName);
		//
		// Current controller/service name.
		$this->assign('name', $this->_name);
		//
		// Current language (if any).
		$this->assign('lang', $Defaults[GC_DEFAULTS_LANGS_DEFAULTLANG]);
	}
	/**
	 * This method builds a cache key based on current parameters.
	 * 
	 * @return string Generated cache key.
	 */
	protected function cacheKey() {
		//
		// Checking if this key was already generated.
		if($this->_cacheKey === false) {
			//
			// A key must start with the current class name.
			$this->_cacheKey = get_called_class();
			//
			// Checking each request method.
			foreach($this->_cacheParams as $method => $names) {
				//
				// Checking each parameter and its current value.
				foreach($names as $name) {
					$value = $this->params->{$method}->{$name};
					$this->_cacheKey.= "_{$name}_{$value}";
				}
			}
		}
		//
		// Returning a generated key.
		return $this->_cacheKey;
	}
	/**
	 * This method check current parameters and looks for those not present.
	 * When a required parameter is not found, an internal error is set.
	 */
	protected function checkParams() {
		//
		// Checking each request method.
		foreach($this->_requiredParams as $method => $params) {
			//
			// Only parameters for request method 'GET' and the
			// current one are checked, the rest are considered to be
			// ok even though they are flag as required.
			if($method == 'GET' || $method == Params::Instance()->server->REQUEST_METHOD) {
				//
				// Checking each parameter and its current value.
				foreach($params as $param) {
					//
					// If it's required and not present an
					// error is set.
					if(!isset($this->params->{$method}->{$param})) {
						$this->setError(HTTPERROR_BAD_REQUEST, "Parameter '{$param}' is not set (".strtoupper($method).')');
					}
				}
			}
		}
	}
	/**
	 * This metod generates a simple cache key prefix to prepend to every
	 * cache key, It may allow quick identification of keys and avoid possible
	 * collitions between cache entry types.
	 * 
	 * @param string $extra
	 * @return type
	 */
	protected function cachePrefix($extra = '') {
		//
		// If this prefix is for a rendered view, it should contain
		// current format too.
		if($extra == self::PrefixRender) {
			$extra = "{$extra}_".strtoupper($this->_format);
		}
		//
		// Retruning a generated prefix.
		return get_called_class()."_{$extra}";
	}
	/**
	 * This method is the one in charge of starting the processing of a
	 * request without any cache interference.
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	protected function dryRun() {
		//
		// Default values.
		$out = false;
		//
		// Detecting current request method.
		$method = Params::Instance()->server->REQUEST_METHOD;
		//
		// If there's a specific method for the current request method,
		// it is run, otherwise the basic run method is called.
		if(method_exists($this, "run{$method}")) {
			$out = $this->{"run{$method}"}();
		} elseif(method_exists($this, 'basicRun')) {
			$out = $this->basicRun();
		} else {
			//
			// If there's no method inside the controller/servive to
			// attend this request, an internal error is set.
			$this->setError(HTTPERROR_NOT_IMPLEMENTED, "Request method '{$method}' is not implemented");
		}
		//
		// Returning execution success result.
		return $out;
	}
	/**
	 * Initializations.
	 */
	protected function init() {
		//
		// When there's a debug parameter in place, cache is disabled
		// unless it is 'debugresetcache.
		if($this->params->hasDebugs() && !isset($this->params->debugresetcache)) {
			$this->_cached = false;
		}
		//
		// This is to avoid cache collision when a controller is
		// requested with different modes.
		$this->_cacheParams['GET'][] = 'mode';
	}
	//
	// Protected class methods.
	/**
	 * Provides access to values shared between controllers/services.
	 *
	 * @param string $key Value to look for.
	 * @return mixed Retruns the value found or NULL when it's not.
	 */
	protected static function GetShare($key) {
		return isset(self::$_Shares[$key]) ? self::$_Shares[$key] : null;
	}
	/**
	 * This method allows to share values between controllers/services.
	 *
	 * @param string $key Key to identify a shared value.
	 * @param mixed $value Value to be associated with the given key.
	 * @return mixed Same as 'Exporter::GetShare()'.
	 */
	protected static function Share($key, $value) {
		self::$_Shares[$key] = $value;

		return self::GetShare($key);
	}
}
