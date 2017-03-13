<?php

/**
 * @class SdocLayoutController
 *
 * Accessible adding '&layout=sdoc_layout'
 */
class SdocLayoutController extends \TooBasic\Layout {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('TOOBASIC_VERSION', TOOBASIC_VERSION);
		$this->assign('TOOBASIC_VERSION_NAME', TOOBASIC_VERSION_NAME);

		return $this->status();
	}
}
