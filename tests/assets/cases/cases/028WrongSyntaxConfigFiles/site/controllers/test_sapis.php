<?php

class TestSapisController extends \TooBasic\Controller {
	protected $_cached = false;
	protected function basicRun() {
		$this->sapireader->broken_api;
		return $this->status();
	}
}
