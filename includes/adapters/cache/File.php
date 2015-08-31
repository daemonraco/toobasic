<?php

/**
 * @file File.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class File
 * This class provides and cache adaptation for entries stored in files.
 */
class File extends Adapter {
	//
	// Constants.
	const SubFolder = 'filecache';
	const CacheExtension = 'data';
	//
	// Public methods.
	/**
	 * This method removes a cache entry. Basically, it removes a cache file.
	 *
	 * @param string $prefix Key prefix of the entry to remove.
	 * @param string $key Key of the entry to remove.
	 */
	public function delete($prefix, $key) {
		@unlink($this->path($prefix, $key));
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
		$path = $this->path($prefix, $key);
		//
		// Cleaning expired.
		$this->cleanPath($path, $delay);

		$data = null;
		if(is_readable($path)) {
			$data = unserialize(file_get_contents($path));
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
		file_put_contents($this->path($prefix, $key, true), serialize($data));
	}
	//
	// Protected methods.
	/**
	 * This methods removes a file containing an expired cache entry.
	 *
	 * @param string $path Path where a cache entry is stored.
	 * @param int $delay Amount of seconds the entry lasts.
	 * @param bool $forced Remove the path regardless of its expiration
	 * status.
	 */
	protected function cleanPath($path, $delay, $forced = false) {
		if(is_readable($path) && ($forced || (time() - filemtime($path)) >= $this->expirationLength($delay))) {
			unlink($path);
		}
	}
	/**
	 * This method creates a proper cache entry key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @param bool $genDir not used
	 * @return string Returns a normalize key.
	 */
	protected function path($prefix, $key, $genDir = false) {
		global $Directories;

		$key = sha1($key);
		$prefix.= ($prefix ? '_' : '');
		$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SubFolder."/{$prefix}{$key}.".self::CacheExtension);

		return $path;
	}
}
