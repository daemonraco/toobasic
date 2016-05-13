<?php

class BasicLayoutEmail extends \TooBasic\EmailLayout {
	protected function basicRun() {
		return $this->status();
	}
	protected function simulation() {
		return true;
	}
}
