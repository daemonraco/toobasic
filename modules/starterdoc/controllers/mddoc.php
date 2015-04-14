<?php

use \TooBasic\Sanitizer as TB_Sanitizer;

class MddocController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = true;
	protected $_layout = "mdlayout";
	//
	// Protected methods.
	protected function basicRun() {
		$out = true;

		$filepath = TB_Sanitizer::DirPath(ROOTDIR."/{$this->params->get->doc}");
		if(is_readable($filepath)) {
			$uri = explode("/", TB_Sanitizer::UriPath($this->params->get->doc));
			array_pop($uri);
			$uri = implode("/", $uri);
			$this->assign("mdDocUri", $uri ? "{$uri}/" : "");

			$this->assign("title", $this->params->get->doc);
			$this->assign("content", "\n".file_get_contents($filepath));
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "File '{$this->params->get->doc}' does not exist");
			$out = false;
		}

		return $out;
	}
	protected function init() {
		parent::init();

		$this->_cacheParams["GET"][] = "doc";
		$this->_requiredParams["GET"][] = "doc";
	}
}
