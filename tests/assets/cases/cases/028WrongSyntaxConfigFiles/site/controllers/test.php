<?php

class TestController extends \TooBasic\Controller {
	protected $_cached = false;
	protected function basicRun() {
		return $this->status();
	}
}
