<?php

/**
 * @file Redis.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

//
// Class aliases.
use TooBasic\Params;

/**
 * @class Redis
 * This class provides and cache adaptation for Redis.
 */
class Redis extends Adapter {
	//
	// Protected properties.
	/**
	 * @var \Predis\Client Redis server connection pointer.
	 */
	protected $_conn = false;
	/**
	 * @var bool This flag gets true when specific debugs are active.
	 */
	protected $_debug = false;
	// 
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @throws \TooBasic\CacheException
	 */
	public function __construct() {
		parent::__construct();
		//
		// Detecting specific debugs activation.
		$this->_debug = isset(Params::Instance()->get->debugredis);
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking configuration.
		if(!isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_SCHEME]) || !isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST]) || !isset($Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT])) {
			throw new \TooBasic\CacheException("Redis is not properly set. Check constants \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT], \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_HOST] and \$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PORT].");
		}
		//
		// Connection to 'Redis' server using 'Predis'.
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
	/**
	 * This method removes a cache entry.
	 *
	 * @param string $prefix Key prefix of the entry to remove.
	 * @param string $key Key of the entry to remove.
	 */
	public function delete($prefix, $key) {
		$fullKey = $this->fullKey($prefix, $key);
		$this->_conn->del($fullKey);

		if($this->_debug) {
			echo "<!-- redis delete: {$fullKey} [NOT FOUND]-->\n";
		}
	}
	/**
	 * This method retieves a cache entry data.
	 *
	 * @param string $prefix Key prefix of the entry to retieve.
	 * @param string $key Key of the entry to retieve.
	 * @param int $delay Amount of seconds the entry lasts.
	 * @return mixed Return the infomation stored in the request cache entry
	 * or NULL if none found.
	 */
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
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
	/**
	 * This method stores information in cache and associates it to a certain
	 * cache key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @param mixed $data Information to store.
	 * @param int $delay Amount of seconds the entry lasts.
	 */
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
	/**
	 * This method creates a proper cache entry key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @return string Returns a normalize key.
	 */
	protected function fullKey($prefix, $key) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Encoding key.
		$key = sha1($key);
		//
		// Adding the prefix.
		$prefix.= ($prefix ? '_' : '');
		//
		// Adding a specific prefix for this Redis connection to avoid 
		// collisions.
		$out = "{$Defaults[GC_DEFAULTS_REDIS][GC_DEFAULTS_REDIS_PREFIX]}_{$prefix}{$key}";

		return $out;
	}
}
