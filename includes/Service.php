<?php

namespace TooBasic;

abstract class Service extends Exporter {
	//
	// Protected properties.
	protected $_headers = array();
	//
	// Magic methods.
	//
	// Public methods.
	public function lastRun() {
		if($this->_lastRun === false) {
			$this->_lastRun = array(
				"status" => $this->_status,
				"data" => $this->_assignments,
				"headers" => $this->_headers,
				"error" => $this->lastError(),
				"errors" => $this->errors()
			);
		}

		return $this->_lastRun;
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		$this->_status = true;

		$this->checkParams();

		$key = $this->cacheKey();
		//
		// Computing cache.
		if($this->_status) {
			$prefixComputing = $this->cachePrefix(Exporter::PrefixService);

			$dataBlock = false;
			if($this->_cached) {
				$dataBlock = $this->cache->get($prefixComputing, $key);
			}
//
			if($dataBlock && !isset($this->params->debugresetcache)) {
				$this->_lastRun = $dataBlock;

				$this->_status = $this->_lastRun["status"];
				$this->_assignments = $this->_lastRun["data"];
				$this->_headers = $this->_lastRun["headers"];
				$this->_lastError = $this->_lastRun["error"];
				$this->_errors = $this->_lastRun["errors"];
			} else {
				$this->autoAssigns();
				$this->_status = $this->dryRun();
//
				if($this->_cached) {
					$this->cache->save($prefixComputing, $key, $this->lastRun());
				}
				$this->_lastRun = false;
			}
		}

		return $this->status();
	}
	protected function setHeader($name, $value) {
		$this->_headers[$name] = $value;
	}
}
