<?php

class TooBasicVirtualController extends \TooBasic\Controller {
	//
	// Protected properties.	
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeDouble;
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
	/**
	 * This metod generates a simple cache key prefix to prepend to every
	 * cache key, It may allow quick identification of keys and avoid possible
	 * collitions between cache entry types.
	 *
	 * @param string $extra
	 * @return type
	 */
	protected function cachePrefix($extra = '') {
		return parent::cachePrefix($extra).'_V'.$this->viewName();
	}
}
