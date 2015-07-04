<?php

class GitlinkController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeLarge;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
}
