<?php

use \TooBasic\Sanitizer as TB_Sanitizer;

class MddocController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	protected $_layout = 'mdlayout';
	//
	// Protected methods.
	protected function basicRun() {
		$filepath = TB_Sanitizer::DirPath(ROOTDIR."/{$this->params->get->doc}");
		if(is_readable($filepath)) {
			$this->assign('title', "Doc: {$this->params->get->doc}");
			$this->assign('content', "\n".file_get_contents($filepath));
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "File '{$this->params->get->doc}' does not exist");
		}

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_cacheParams['GET'][] = 'doc';
		$this->_requiredParams['GET'][] = 'doc';
	}
}
