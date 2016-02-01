<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
	protected function setUp() {
		$this->setBrowser('firefox');
		$this->setBrowserUrl('http://localhost/');
	}
	public function testTitle() {
		$this->url('index.php');
		$this->assertEquals('TooBasic', $this->title());
	}
}
