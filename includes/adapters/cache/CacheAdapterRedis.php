<?php

/**
 * @file CacheAdapterRedis.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class CacheAdapterRedis
 */
class CacheAdapterRedis extends CacheAdapter {
	//
	// Protected properties.
	protected $_conn = false;
	protected $_debug = false;
	// 
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->_debug = isset(Params::Instance()->get->debugredis);

		global $Defaults;

		if(!isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_SCHEME]) || !isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST]) || !isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT])) {
			throw new \TooBasic\CacheException("Redis is not properly set. Check constants \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT], \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST] and \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT].");
		}

		try {
			$this->_conn = new \Predis\Client(array(
				'scheme' => $Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_SCHEME],
				'host' => $Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST],
				'port' => $Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT])
			);
		} catch(\Exception $e) {
			throw new \TooBasic\CacheException("Couldn't connected to Redis. {$e->getMessage()}", $e->getCode(), $e);
		}
	}
	//
	// Public methods.
	public function delete($prefix, $key) {
		$fullKey = $this->fullKey($prefix, $key);
		$data = $this->_conn->del($fullKey);

		if($this->_debug) {
			echo "<!-- redis delete: {$fullKey} [NOT FOUND]-->\n";
		}
	}
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
		$data = null;
		$fullKey = $this->fullKey($prefix, $key);

		if($this->_debug) {
			echo "<!-- redis get: {$fullKey} [SEARCHING...]-->\n";
		}
		$data = $this->_conn->get($fullKey);
		if($data) {
			$data = unserialize($data);

			if($this->_debug) {
				echo "<!-- redis get: {$fullKey} [FOUND]-->\n";
			}
		} else {
			$data = null;

			if($this->_debug) {
				echo "<!-- redis get: {$fullKey} [NOT FOUND]-->\n";
			}
		}

		return $data;
	}
	public function save($prefix, $key, $data, $delay = self::ExpirationSizeLarge) {
		$fullKey = $this->fullKey($prefix, $key);
		$this->_conn->del($fullKey);
		$this->_conn->set($fullKey, serialize($data));
		$this->_conn->expire($fullKey, time() + $this->expirationLength($delay));

		if($this->_debug) {
			echo "<!-- redis set: {$fullKey} -->\n";
		}
	}
	//
	// Protected methods.
	protected function fullKey($prefix, $key) {
		global $Defaults;

		$key = sha1($key);
		$prefix.= ($prefix ? "_" : "");
		$out = "{$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PREFIX]}_{$prefix}{$key}";

		return $out;
	}
}
