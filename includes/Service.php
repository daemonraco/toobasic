<?php

/**
 * @file Service.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class Service
 * This is the basic representation of any service class inside TooBasic.
 */
abstract class Service extends Exporter {
	//
	// Protected properties.
	/**
	 * @var string[] Allowed headers by CORS policies of this service.
	 */
	protected $_corsAllowHeaders = [
		'Accept',
		'Content-Type'
	];
	/**
	 * @var string[] Allowed request by CORS policies of this service.
	 */
	protected $_corsAllowMethods = [];
	/**
	 * @var string[] Allowed sites by CORS policies of this service.
	 */
	protected $_corsAllowOrigin = [];
	/**
	 * @var string[string] CORS specifications.
	 */
	protected $_corsSpecs = false;
	/**
	 * @var string[string] List of to be set on a response.
	 */
	protected $_headers = [];
	/**
	 * @var string[] List of attended methods.
	 */
	protected $_methods = false;
	/**
	 * @var mixed This is a parameter that avoids cache and is useful to track
	 * multiple calls.
	 */
	protected $_transaction = false;
	//
	// Public methods.
	/**
	 * This method returns a full specification of CORS configurations useful
	 * for checks an interface explanation.
	 *
	 * @return mixed[string] Returns a specification.
	 */
	public function corsSpecs() {
		//
		// Is it already analyzed?
		if($this->_corsSpecs === false) {
			//
			// Global dependencies.
			global $Defaults;
			//
			// If the service didn't configure a specific list of
			// methods, it uses its list of allowed methods.
			if(!$this->_corsAllowMethods) {
				$this->_corsAllowMethods = $this->methods();
			}
			//
			// Options is always a method.
			$this->_corsAllowMethods[] = 'OPTIONS';
			//
			// Merging the list of allowed sites configured for all
			// services and sites internally confgured by the service.
			$this->_corsAllowOrigin = array_merge($Defaults[GC_DEFAULTS_SERVICE_ALLOWEDSITES], $this->_corsAllowOrigin);
			//
			// Adding specific sites for this service.
			if(isset($Defaults[GC_DEFAULTS_SERVICE_ALLOWEDBYSRV][$this->name()])) {
				$this->_corsAllowOrigin = array_merge($Defaults[GC_DEFAULTS_SERVICE_ALLOWEDBYSRV][$this->name()], $this->_corsAllowOrigin);
			}
			//
			// Cleaning values.
			$this->_corsAllowHeaders = array_values(array_unique($this->_corsAllowHeaders));
			sort($this->_corsAllowHeaders);
			$this->_corsAllowMethods = array_values(array_unique($this->_corsAllowMethods));
			sort($this->_corsAllowMethods);
			$this->_corsAllowOrigin = array_values(array_unique($this->_corsAllowOrigin));
			sort($this->_corsAllowOrigin);
			//
			// Building the specification structure.
			$this->_corsSpecs = [
				GC_AFIELD_HEADERS => $this->_corsAllowHeaders,
				GC_AFIELD_METHODS => $this->_corsAllowMethods,
				GC_AFIELD_ORIGINS => $this->_corsAllowOrigin
			];
		}

		return $this->_corsSpecs;
	}
	/**
	 * Provides access to the last processing results.
	 *
	 * @return mixed[string] Processing results.
	 */
	public function lastRun() {
		//
		// If there's no results yet it is generated.
		if($this->_lastRun === false) {
			$this->_lastRun = [
				GC_AFIELD_STATUS => $this->_status,
				GC_AFIELD_DATA => $this->_assignments,
				GC_AFIELD_HEADERS => $this->_headers,
				GC_AFIELD_ERROR => $this->lastError(),
				GC_AFIELD_ERRORS => $this->errors()
			];
		}
		//
		// Setting current transaction ID.
		$this->_lastRun[GC_AFIELD_TRANSACTION] = $this->_transaction;
		//
		// Returning las run results
		return $this->_lastRun;
	}
	public function methods() {
		//
		// Is it already analyzed?
		if($this->_methods === false) {
			//
			// Default values.
			$this->_methods = [];
			//
			// Creating a reflexion object to analyse a this service.
			$reflectionObject = new \ReflectionClass($this);
			//
			// Looking for method that attend requests.
			$matches = false;
			foreach($reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED) as $reflection) {
				if(preg_match('/^run(?<method>[A-Z]+)$/', $reflection->name, $matches)) {
					$this->_methods[] = $matches['method'];
				} elseif($reflection->name == 'basicRun') {
					$this->_methods[] = 'GET';
				}
			}
			//
			// Cleaning values.
			$this->_methods = array_unique($this->_methods);
			sort($this->_methods);
		}

		return $this->_methods;
	}
	/**
	 * This is the point of entry to execute a service class. It is in charge
	 * of loading and saving cache and also executing the real logic of the
	 * service.
	 *
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		//
		// It's a new execution so we assume the status is ok at this
		// point.
		$this->_status = true;
		//
		// Checking parameters.
		$this->checkParams();
		//
		// Obtaining a proper cache key.
		$key = $this->cacheKey();
		//
		// If everything is ok, the service should be analyzed.
		if($this->_status) {
			//
			// Obtaining a proper cache key prefix.
			$prefixComputing = $this->cachePrefix(Exporter::PrefixService);
			//
			// Cached data block default.
			$dataBlock = false;
			//
			// Cheching if this serice uses cache.
			if($this->_cached && !isset($this->params->debugresetcache)) {
				//
				// Obtaining a cache entry.
				$dataBlock = $this->cache->get($prefixComputing, $key);
			}
			//
			// Checking if something was obtained from cache or not.
			if($dataBlock) {
				//
				// Loading cached data block as last result.
				$this->_lastRun = $dataBlock;
				//
				// Obtaining specific service properties from the
				// cache data block.
				$this->_status = $this->_lastRun[GC_AFIELD_STATUS];
				$this->_assignments = $this->_lastRun[GC_AFIELD_DATA];
				$this->_headers = $this->_lastRun[GC_AFIELD_HEADERS];
				$this->_lastError = $this->_lastRun[GC_AFIELD_ERROR];
				$this->_errors = $this->_lastRun[GC_AFIELD_ERRORS];
			} else {
				//
				// At this point, there was no cached data block.
				//
				// Adding automatic assignments.
				$this->autoAssigns();
				//
				// Running service's logic.
				$this->_status = $this->dryRun();
				//
				// Generating CORS flags.
				$this->addCorsHeaders();
				//
				// At this point we suppose the last run property
				// is outdated and needs to be regenerated.
				$this->_lastRun = false;
				//
				// Cheching if this serice uses cache.
				if($this->_cached) {
					//
					// Storing the last run results on cache.
					$this->cache->save($prefixComputing, $key, $this->lastRun());
				}
			}
		}
		//
		// Returning the final status.
		return $this->status();
	}
	//
	// Protected methods.
	/**
	 * This method adds CORS headers to every response.
	 */
	protected function addCorsHeaders() {
		//
		// Obtaining proper specifications.
		$specs = $this->corsSpecs();
		//
		// Checking origin allowance.
		$origin = $this->params->headers->Origin;
		$allowOrigin = '';
		foreach($specs[GC_AFIELD_ORIGINS] as $or) {
			if(preg_match('~^'.str_replace('*', '(.*)', $or).'$~', $origin)) {
				$allowOrigin = $origin;
				break;
			}
		}
		//
		// Adding headers to be send.
		$this->_headers['Access-Control-Allow-Origin'] = $allowOrigin;
		$this->_headers['Access-Control-Allow-Headers'] = implode(',', $specs[GC_AFIELD_HEADERS]);
		$this->_headers['Access-Control-Allow-Methods'] = implode(',', $specs[GC_AFIELD_METHODS]);
	}
	/**
	 * Class initializer.
	 */
	protected function init() {
		//
		// Forwarding call.
		parent::init();
		//
		// Catching transaction id.
		$this->_transaction = isset($this->params->get->transaction) ? $this->params->get->transaction : false;
	}
	/**
	 * This method is always present because it is used by CORS policies.
	 *
	 * @return boolean Unless there was a prior error, this always returns
	 * true.
	 */
	protected function runOPTIONS() {
		return $this->status();
	}
	/**
	 * Sets a header value to be send on responses.
	 *
	 * @param string $name Header's name.
	 * @param value $value Header's value.
	 */
	protected function setHeader($name, $value) {
		$this->_headers[$name] = $value;
	}
}
