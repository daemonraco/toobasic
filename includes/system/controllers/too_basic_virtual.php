<?php

class TooBasicVirtualController extends \TooBasic\Controller {
	//
	// Constants.
	//
	// Public class properties.
	//
	// Protected class properties.
	//
	// Protected properties.	
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeLarge;
	//
	// Magic methods.
	//
	// Public methods.
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
	//
	// Public class methods.
	//
	// Protected class methods.
}
