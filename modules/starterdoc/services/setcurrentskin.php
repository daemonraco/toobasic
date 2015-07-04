<?php

class SetcurrentskinService extends \TooBasic\Service {
	//
	// Public methods.
	protected function runPOST() {
		$out = true;

		global $SkinName;
		\TooBasic\setSessionSkin($this->params->post->setskin);

		$this->assign("skin", $_SESSION[GC_SESSION_SKIN]);

		return $out;
	}
	protected function init() {
		parent::init();

		$this->_requiredParams["POST"][] = "setskin";
	}
}
