<?php

class Selenium_ControllerSystoolTest extends TooBasic_SeleniumTestCase {
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	public function testCreateController() {
		passthru('php shell.php sys controller new hello_world');

		$this->assertTrue(is_file(TESTS_ROOTDIR."/site/controllers/hello_world.php"), 'Controller file was not created');
		$this->assertTrue(is_file(TESTS_ROOTDIR."/site/templates/action/hello_world.html"), 'View file was not created');
	}
	public function testAccessTheControllerByUrlWithParameters() {
		$this->url('/?action=hello_world');
		$this->checkCurrentSource();

		$html = $this->source();
		$this->assertRegExp('/HelloWorldController/', $html, "Page does not contain the keyword 'HelloWorldController'.");
	}
	public function testAccessTheControllerByRoute() {
		$this->url('/hello_world');
		$this->checkCurrentSource();

		$html = $this->source();
		$this->assertRegExp('/HelloWorldController/', $html, "Page does not contain the keyword 'HelloWorldController'.");
	}
}
