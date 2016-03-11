<?php

class LayoutTesterController extends \TooBasic\Controller {
	protected $_cached = true;
	public function basicRun() {
		return $this->status();
	}
}
