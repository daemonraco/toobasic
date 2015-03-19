<?php

namespace TooBasic;

abstract class CacheAdapter extends Adapter {
	//
	// Constants.
	//
	// Protected properties.
	//
	// Public methods.
	abstract public function delete($prefix, $key);
	abstract public function get($prefix, $key);
	abstract public function save($prefix, $key, $data);
	//
	// Protected methods.
}
