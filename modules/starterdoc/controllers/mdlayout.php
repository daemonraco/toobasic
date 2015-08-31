<?php

use TooBasic\Sanitizer as TB_Sanitizer;

class MdlayoutController extends TooBasic\Layout {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$uri = explode('/', TB_Sanitizer::UriPath($this->params->get->doc));
		array_pop($uri);
		$uri = implode('/', $uri);
		$this->assign('mdDocUri', $uri ? "{$uri}/" : '');

		return true;
	}
	protected function init() {
		parent::init();
		$this->_cacheParams['GET'][] = 'doc';
	}
}
