<?php

class Selenium_TooBasicTest extends TooBasic_SeleniumTestCase {
	public function testTitle() {
		$this->url('index.php');
		$this->assertRegExp('/TooBasic/', $this->title(), "Page title doesn't have the keyword 'TooBasic'.");
		$this->assertNotRegExp('/Exception/', $this->title(), 'Page seems to be showing an exception.');
	}
}
