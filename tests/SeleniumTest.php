<?php
class WebTest extends PHPUnit_Extensions_Selenium2TestCase {
	protected function setUp() {
		$this->setBrowser('firefox');
		$this->setBrowserUrl('http://toobasic.daemonraco.com/');
	}
	public function testTitle() {
		$this->url('index.php');
		$this->assertEquals('TooBasic-1.0.4-serpent', $this->title());
	}
}
