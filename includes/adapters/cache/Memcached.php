<?php

/**
 * @file Memcached.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

use TooBasic\Params;

/**
 * @class Memcached
 */
class Memcached extends Adapter {
	//
	// Protected properties.
	protected $_conn = false;
	// 
	// Magic methods.
	public function __construct() {
		parent::__construct();
		global $Defaults;

		if(!isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER]) || !isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT]) || !isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX])) {
			throw new \TooBasic\CacheException("Memcached is not properly set. Check constants \$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER], \$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT] and \$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX].");
		}

		$this->_conn = new \Memcached();
		$this->_conn->addServer($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER], $Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT]);
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
		$data = $this->_conn->delete($fullKey);

		if(isset(Params::Instance()->get->debugmemcached)) {
			echo "<!-- memcached delete: {$fullKey} [NOT FOUND]-->\n";
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
		$data = null;
		$fullKey = $this->fullKey($prefix, $key);

		$data = $this->_conn->get($fullKey);
		if($data) {
			$data = unserialize($data);

			if(isset(Params::Instance()->get->debugmemcached)) {
				echo "<!-- memcached get: {$fullKey} [FOUND]-->\n";
			}
		} else {
			$data = null;

			if(isset(Params::Instance()->get->debugmemcached)) {
				echo "<!-- memcached get: {$fullKey} [NOT FOUND]-->\n";
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
		$this->_conn->delete($fullKey);
		$this->_conn->set($fullKey, serialize($data), time() + $this->expirationLength($delay));

		if(isset(Params::Instance()->get->debugmemcached)) {
			echo "<!-- memcached set: {$fullKey} -->\n";
		}
	}
	//
	// Protected methods.
	protected function fullKey($prefix, $key) {
		global $Defaults;

		$key = sha1($key);
		$prefix.= ($prefix ? '_' : '');
		$out = "{$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX]}_{$prefix}{$key}";

		return $out;
	}
}
