<?php

/**
 * @class SdocHomeController
 *
 * Accessible at '?action=sdoc_home'
 */
class SdocHomeController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	protected $_layout = 'sdoc_layout';
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('TOOBASIC_VERSION', TOOBASIC_VERSION);
		$this->assign('TOOBASIC_VERSION_NAME', TOOBASIC_VERSION_NAME);

		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
