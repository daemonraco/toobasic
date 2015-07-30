<?php

class GitlinkController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
}
