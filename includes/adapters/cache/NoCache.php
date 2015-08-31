<?php

/**
 * @file Cache.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class NoCache
 * This class represent a cache adapter that acts as a dummy and stores nothing.
 * It provides a way to safely disable cache.
 */
class NoCache extends Adapter {
	//
	// Public methods.
	/**
	 * This method removes a cache entry.
	 *
	 * @param string $prefix Key prefix of the entry to remove.
	 * @param string $key Key of the entry to remove.
	 */
	public function delete($prefix, $key) {
		// No cache hence nothing to delete.
	}
	/**
	 * This method retieves a cache entry data.
	 *
	 * @param string $prefix Key prefix of the entry to retieve.
	 * @param string $key Key of the entry to retieve.
	 * @param int $delay Amount of seconds the entry lasts. This parameter
	 * isn't required in this adapter, but it present for compatibility.
	 * @return mixed Return the infomation stored in the request cache entry
	 * or NULL if none found.
	 */
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
		// No cache hence nothing to return.
		return null;
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
		// No cache hence nothing to save.
	}
}
