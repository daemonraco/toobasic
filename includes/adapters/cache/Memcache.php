<?php

/**
 * @file Memcache.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

use TooBasic\Params;

class Memcache extends Adapter {
	//
	// Protected properties.
	protected $_conn = false;
	protected $_compress = false;
	// 
	// Magic methods.
	public function __construct() {
		parent::__construct();
		global $Defaults;

		if(!isset($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_SERVER]) || !isset($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PORT]) || !isset($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PREFIX])) {
			throw new \TooBasic\CacheException("Memcache is not properly set. Check constants \$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_SERVER], \$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PORT] and \$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PREFIX].");
		}

		if(!isset($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_COMPRESSED])) {
			$this->_compress = 0;
		} else {
			$this->_compress = boolval($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_COMPRESSED]) ? MEMCACHE_COMPRESSED : 0;
		}

		$this->_conn = new \Memcache();
		$this->_conn->connect($Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_SERVER], $Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PORT]);
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
		$this->_conn->delete($fullKey);

		if(isset(Params::Instance()->get->debugmemcache)) {
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

			if(isset(Params::Instance()->get->debugmemcache)) {
				echo "<!-- memcached get: {$fullKey} [FOUND]-->\n";
			}
		} else {
			$data = null;

			if(isset(Params::Instance()->get->debugmemcache)) {
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
		$this->_conn->set($fullKey, serialize($data), $this->_compress, time() + $this->expirationLength($delay));

		if(isset(Params::Instance()->get->debugmemcache)) {
			echo "<!-- memcached set: {$fullKey} -->\n";
		}
	}
	//
	// Protected methods.
	protected function fullKey($prefix, $key) {
		global $Defaults;

		$key = sha1($key);
		$prefix.= ($prefix ? "_" : "");
		$out = "{$Defaults[GC_DEFAULTS_MEMCACHE][GC_DEFAULTS_MEMCACHE_PREFIX]}_{$prefix}{$key}";

		return $out;
	}
}
