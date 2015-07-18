<?php

/**
 * @file CacheAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class CacheAdapter
 * @abstract
 */
abstract class CacheAdapter extends Adapter {
	//
	// Constants.
	const ExpirationSizeDouble = 'double';
	const ExpirationSizeLarge = 'large';
	const ExpirationSizeMedium = 'medium';
	const ExpirationSizeSmall = 'small';
	//
	// Protected properties.
	protected $_expirationLength = 3600;
	//
	// Public methods.
	abstract public function delete($prefix, $key);
	abstract public function get($prefix, $key, $delay = self::ExpirationSizeLarge);
	abstract public function save($prefix, $key, $data, $delay = self::ExpirationSizeLarge);
	//
	// Protected methods.
	protected function expirationLength($delay) {
		$out = $this->_expirationLength;

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
	protected function init() {
		global $Defaults;
		$this->_expirationLength = $Defaults[GC_DEFAULTS_CACHE_EXPIRATION];
	}
}
