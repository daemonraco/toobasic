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
	 * @var string[string] List of to be set on a response.
	 */
	protected $_headers = array();
	//
	// Public methods.
	/**
	 * Provides access to the last processing results.
	 *
	 * @return mixed[string] Processing results.
	 */
	public function lastRun() {
		//
		// If there's no results yet it is generated.
		if($this->_lastRun === false) {
			$this->_lastRun = array(
				'status' => $this->_status,
				'data' => $this->_assignments,
				'headers' => $this->_headers,
				'error' => $this->lastError(),
				'errors' => $this->errors()
			);
		}
		//
		// Returning las run results
		return $this->_lastRun;
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
				$this->_status = $this->_lastRun["status"];
				$this->_assignments = $this->_lastRun["data"];
				$this->_headers = $this->_lastRun["headers"];
				$this->_lastError = $this->_lastRun["error"];
				$this->_errors = $this->_lastRun["errors"];
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
	 * Sets a header value to be send on responses.
	 *
	 * @param string $name Header's name.
	 * @param value $value Header's value.
	 */
	protected function setHeader($name, $value) {
		$this->_headers[$name] = $value;
	}
}
