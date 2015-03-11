<?php

class CacheAdapterFile extends CacheAdapter {
	//
	// Constants.
	const SubFolder = "filecache";
	const CacheExtension = "data";
	//
	// Protected properties.
	//
	// Public methods.
	public function delete($prefix, $key) {
		@unlink($this->path($prefix, $key));
	}
	public function get($prefix, $key) {
		$path = $this->path($prefix, $key);

		$this->cleanPath($path);

		$data = null;
		if(is_readable($path)) {
			$data = unserialize(file_get_contents($path));
		}

		return $data;
	}
	public function save($prefix, $key, $data) {
		file_put_contents($this->path($prefix, $key, true), serialize($data));
	}
	//
	// Protected methods.
	protected function cleanPath($path, $forced = false) {
		if(is_readable($path) && ($forced || (time() - filemtime($path)) >= 3600)) {
			unlink($path);
		}
	}
	protected function path($prefix, $key, $genDir = false) {
		global $Directories;

		$key = sha1($key);
		$prefix.= ($prefix ? "_" : "");
		$path = Sanitizer::DirPath("{$Directories["cache"]}/".self::SubFolder."/{$prefix}{$key}.".self::CacheExtension);

		return $path;
	}
}
