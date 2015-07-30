<?php

namespace TooBasic\Adapters\Cache;

class File extends Adapter {
	//
	// Constants.
	const SubFolder = 'filecache';
	const CacheExtension = 'data';
	//
	// Public methods.
	public function delete($prefix, $key) {
		@unlink($this->path($prefix, $key));
	}
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
		$path = $this->path($prefix, $key);

		$this->cleanPath($path, $delay);

		$data = null;
		if(is_readable($path)) {
			$data = unserialize(file_get_contents($path));
		}

		return $data;
	}
	public function save($prefix, $key, $data, $delay = self::ExpirationSizeLarge) {
		file_put_contents($this->path($prefix, $key, true), serialize($data));
	}
	//
	// Protected methods.
	protected function cleanPath($path, $delay, $forced = false) {
		if(is_readable($path) && ($forced || (time() - filemtime($path)) >= $this->expirationLength($delay))) {
			unlink($path);
		}
	}
	protected function path($prefix, $key, $genDir = false) {
		global $Directories;

		$key = sha1($key);
		$prefix.= ($prefix ? '_' : '');
		$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SubFolder."/{$prefix}{$key}.".self::CacheExtension);

		return $path;
	}
}
