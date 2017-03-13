<?php

class GitlinkController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
}
