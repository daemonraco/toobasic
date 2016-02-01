<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
	protected function setUp() {
		$this->setHost('localhost');
		$this->setPort('80');
		$this->setBrowser('firefox');
		$this->setBrowserUrl('http://localhost:80/');
	}
	public function testTitle() {
		$this->url('index.php');
		$this->assertEquals('TooBasic', $this->title());
	}
}
