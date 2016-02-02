<?php

class TooBasicTest extends TooBasic_SeleniumTestCase {
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	public function testCreateController() {
		global $Directories;
		global $Paths;
		passthru('php shell.php sys controller new hello_world');

		$this->assertTrue(is_file("{$Directories[GC_DIRECTORIES_SITE]}{$Paths[GC_PATHS_CONTROLLERS]}/hello_world.php"), 'Controller file was not created');
		$this->assertTrue(is_file("{$Directories[GC_DIRECTORIES_SITE]}{$Paths[GC_PATHS_TEMPLATES]}/action/hello_world.html"), 'View file was not created');
	}
	public function testAccessTheControllerByUrlWithParameters() {
		$this->url('/?action=hello_world');
		$html = $this->source();

		$this->assertTrue(preg_match('/HelloWorldController/', $html) ? true : false, "Page does not contain the keyword 'HelloWorldController'.");
	}
	public function testAccessTheControllerByRoute() {
		$this->url('/hello_world');
		$html = $this->source();

		$this->assertTrue(preg_match('/HelloWorldController/', $html) ? true : false, "Page does not contain the keyword 'HelloWorldController'.");
	}
}
