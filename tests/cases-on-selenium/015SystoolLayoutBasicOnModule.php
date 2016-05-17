<?php

require_once __DIR__.'/XXXSystoolLayoutAs.php';

class SystoolLayoutBasicOnModuleTest extends Selenium_SystoolLayoutAsTest {
	//
	// Internal properties.
	protected $_controllerFiles = [
		'/site/configs/routes.json',
		'/site/controllers/home.php',
		'/site/templates/action/home.html'
	];
	protected $_controllerStickyFiles = [
		'/site/configs/routes.json'
	];
	protected $_layoutFiles = [
		'/modules/mymodule/configs/config_http.php',
		'/modules/mymodule/controllers/basic.php',
		'/modules/mymodule/scripts/script.js',
		'/modules/mymodule/styles/style.css',
		'/modules/mymodule/templates/action/basic.html'
	];
	protected $_layoutStickyFiles = [
		'/modules/mymodule/configs/config_http.php'
	];
	//
	// Creation @{
	public function testCreatingAController() {
		$this->createController();
	}
	public function testCreatingTheLayout() {
		$this->createLayout('--type basic --module mymodule');
	}
	// @}
	//
	// Checks @{
	public function testCheckingPageRender() {
		parent::testCheckingPageRender();
	}
	// @}
	//
	// Removal @{
	public function testRemovingTheLayout() {
		$this->removeLayout('--type basic --module mymodule');
	}
	public function testRemovingTheController() {
		$this->removeController();
	}
	// @}
	//
	// Internal methods @{
	protected function validateBody($body) {
		$this->assertTrue(boolval($body), 'Unable to find the body or maybe it was not generated in the right way.');

		$contents = $body->elements($this->using('css selector')->value('div#MainContents'));

		$this->assertEquals(1, count($contents), 'Unable to find a contents section in the body.');
		$this->assertRegExp('~(.*)home(.*)HomeController(.*)~m', $contents[0]->text(), 'Main content is not the expected controller.');
	}
	// @}
}
