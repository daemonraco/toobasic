<?php

class TooBasicVirtualEmail extends \TooBasic\Email {
	//
	// Protected methods.
	protected function basicRun() {
		return true;
	}
	protected function simulation() {
		return true;
	}
}
