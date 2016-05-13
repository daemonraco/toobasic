<?php

class FixedUnknownLayoutController extends \TooBasic\Controller {
	protected $_layout = 'unknown_layout';
	protected function basicRun() {
		return $this->status();
	}
}
