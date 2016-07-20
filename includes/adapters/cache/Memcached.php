<?php

/**
 * @file Memcached.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

//
// Class aliases.
use TooBasic\CacheException;
use TooBasic\Params;
use TooBasic\Translate;

/**
 * @class Memcached
 * This class provides and cache adaptation for Memcached.
 */
class Memcached extends Adapter {
	//
	// Protected properties.
	/**
	 * @var \Memcached Memcached server connection pointer.
	 */
	protected $_conn = false;
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
		// Checking libraries.
		if(!class_exists('Memcached')) {
			throw new CacheException(Translate::Instance()->EX_class_requires_class([
				'class' => __CLASS__,
				'requirement' => 'Memcached'
			]));
		}
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking configuration.
		if(!isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_SERVER]) || !isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PORT]) || !isset($Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX])) {
			throw new CacheException(Translate::Instance()->EX_MEMCACHED_not_properly_set);
		}
		//
		// Connection to 'Memcached' server.
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
		$this->_conn->delete($fullKey);

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
		// Adding a specific prefix for this Memcached connection to avoid
		// collisions.
		$out = "{$Defaults[GC_DEFAULTS_MEMCACHED][GC_DEFAULTS_MEMCACHED_PREFIX]}_{$prefix}{$key}";

		return $out;
	}
}
