<?php

namespace TooBasic;

class CacheAdapterNoCache extends CacheAdapter {
	//
	// Public methods.
	public function delete($prefix, $key) {
		// No cache hence nothing to delete.
	}
	public function get($prefix, $key) {
		// No cache hence nothing to return.
		return null;
	}
	public function save($prefix, $key, $data) {
		// No cache hence nothing to save.
	}
}
