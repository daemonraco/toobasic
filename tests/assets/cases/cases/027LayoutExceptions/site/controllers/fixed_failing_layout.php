<?php

class FixedFailingLayoutController extends \TooBasic\Controller {
	protected $_layout = 'broken_layout';
	protected function basicRun() {
		return $this->status();
	}
}
