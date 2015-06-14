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
}
