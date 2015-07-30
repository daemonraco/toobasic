<?php

/**
 * @file CacheAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class Adapter
 * @abstract
 * This class represent a basic adapter for cache connection. Each of its
 * specifications must provide the necessary code to add, remove and retrieve
 * entries from a cache engine.
 */
abstract class Adapter extends \TooBasic\Adapters\Adapter {
	//
	// Constants.
	const ExpirationSizeDouble = 'double';
	const ExpirationSizeLarge = 'large';
	const ExpirationSizeMedium = 'medium';
	const ExpirationSizeSmall = 'small';
	//
	// Protected properties.
	/**
	 * @var int This is the basic amount of seconds for expiration time on
	 * each cache entry.
	 */
	protected $_expirationLength = 3600;
	//
	// Public methods.
	/**
	 * This method removes a cache entry.
	 *
	 * @param string $prefix Key prefix of the entry to remove.
	 * @param string $key Key of the entry to remove.
	 */
	abstract public function delete($prefix, $key);
	/**
	 * This method retieves a cache entry data.
	 *
	 * @param string $prefix Key prefix of the entry to retieve.
	 * @param string $key Key of the entry to retieve.
	 * @param int $delay Amount of seconds the entry lasts.
	 * @return mixed Return the infomation stored in the request cache entry
	 * or NULL if none found.
	 */
	abstract public function get($prefix, $key, $delay = self::ExpirationSizeLarge);
	/**
	 * This method stores information in cache and associates it to a certain
	 * cache key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @param mixed $data Information to store.
	 * @param int $delay Amount of seconds the entry lasts.
	 */
	abstract public function save($prefix, $key, $data, $delay = self::ExpirationSizeLarge);
	//
	// Protected methods.
	/**
	 * This method determines the right amount of seconds for a cache entry
	 * expiration time based on the general expiration time and a multiplier.
	 *
	 * @param string $delay Name of the multiplier to use. If unknwon it will
	 * always be considered as Adapter::ExpirationSizeLarge.
	 * @return int Returns an amount of seconds.
	 */
	protected function expirationLength($delay) {
		//
		// Default values.
		$out = $this->_expirationLength;
		//
		// Guessing the right amount.
		switch($delay) {
			case self::ExpirationSizeMedium:
				$out = ceil($this->_expirationLength / 2);
				break;
			case self::ExpirationSizeSmall:
				$out = ceil($this->_expirationLength / 4);
				break;
			case self::ExpirationSizeDouble:
				$out = $this->_expirationLength * 2;
				break;
			case self::ExpirationSizeLarge:
			default:
				$out = $this->_expirationLength;
				break;
		}

		return $out;
	}
	/**
	 * This is the singleton's initializer.
	 */
	protected function init() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// If there's an expiration length configured it must be used.
		if(isset($Defaults[GC_DEFAULTS_CACHE_EXPIRATION])) {
			$this->_expirationLength = $Defaults[GC_DEFAULTS_CACHE_EXPIRATION];
		}
	}
}
