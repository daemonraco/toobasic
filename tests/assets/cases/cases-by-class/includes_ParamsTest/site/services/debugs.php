<?php

class DebugsService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('has_debugs_is_bool', is_bool($this->params->hasDebugs()));
		$this->assign('debugs_is_array', is_array($this->params->debugs()));

		return $this->status();
	}
}
