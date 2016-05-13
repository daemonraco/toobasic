<?php

class FixedFailingLayoutController extends \TooBasic\Controller {
	protected $_layout = 'failing_layout';
	protected function basicRun() {
		return $this->status();
	}
}
