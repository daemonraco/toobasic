<?php

class FixedBrokenLayoutController extends \TooBasic\Controller {
	protected $_layout = 'broken_layout';
	protected function basicRun() {
		return $this->status();
	}
}
