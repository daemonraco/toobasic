<?php

/**
 * @file TooBasicVirtualController.php
 * @author Alejandro Dario Simi
 */
use \TooBasic\Adapters\Cache\Adapter as TB_CacheAdapter;

/**
 * @class TooBasicVirtualController
 */
class TooBasicVirtualController extends \TooBasic\Controller {
	//
	// Protected properties.	
	protected $_cached = TB_CacheAdapter::EXPIRATION_SIZE_DOUBLE;
	//
	// Protected methods.
	protected function basicRun() {
		return $this->status();
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
