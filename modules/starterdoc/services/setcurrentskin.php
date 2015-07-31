<?php

class SetcurrentskinService extends \TooBasic\Service {
	//
	// Public methods.
	protected function runPOST() {
		\TooBasic\setSessionSkin($this->params->post->setskin);
		$this->assign("skin", \TooBasic\guessSkin());

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams["POST"][] = "setskin";
	}
}
