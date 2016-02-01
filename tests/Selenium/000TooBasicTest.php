<?php

class TooBasicTest extends PHPUnit_Extensions_Selenium2TestCase {
	protected function setUp() {
		$this->setHost('localhost');
		$this->setPort(4444);
		$this->setBrowser('firefox');
		$this->setBrowserUrl('http://localhost/');
	}
	public function testTitle() {
		$this->url('index.php');
		$this->assertRegExp('/TooBasic/', $this->title(), "Page title doesn't have the keyword 'TooBasic'.");
		$this->assertNotRegExp('/Exception/', $this->title(), 'Page seems to be showing an exception.');
	}
}
