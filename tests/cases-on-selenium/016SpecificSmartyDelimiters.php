<?php

class SpecificSmartyDelimitersTest extends TooBasic_SeleniumTestCase {
	//
	// Test cases @{
	public function testAssetsCreation() {
		$this->runCommand('php shell.php sys layout create deflayout --type bootstrap');
		$this->runCommand('php shell.php sys controller create hello');

		$this->url('/');
		$this->checkCurrentSource();
	}
	// @}
}
