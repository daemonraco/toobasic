<?php

abstract class Selenium_SystoolLayoutAsTest extends TooBasic_SeleniumTestCase {
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
		'/site/configs/config_http.php',
		'/site/controllers/basic.php',
		'/site/scripts/script.js',
		'/site/styles/style.css',
		'/site/templates/action/basic.html'
	];
	protected $_layoutStickyFiles = [
		'/site/configs/config_http.php'
	];
	//
	// Checks @{
	public function testCheckingPageRender() {
		$this->url('?action=home');
		$this->checkCurrentSource();

		$body = $this->byCssSelector('body.Action_home');

		$this->validateHead();
		$this->validateBody($body);
	}
	// @}
	//
	// Internal methods @{
	protected function createController($extraParams = '') {
		$this->runCommand(trim("php shell.php sys controller create home {$extraParams}"));

		foreach($this->_controllerFiles as $path) {
			$this->assertTrue(is_file(TESTS_ROOTDIR.$path), "File '".TESTS_ROOTDIR."{$path}' was not generated.");
		}
	}
	protected function createLayout($extraParams = '') {
		$this->runCommand(trim("php shell.php sys layout create basic {$extraParams}"));

		foreach($this->_layoutFiles as $path) {
			$this->assertTrue(is_file(TESTS_ROOTDIR.$path), "File '".TESTS_ROOTDIR."{$path}' was not generated.");
		}
	}
	protected function removeController($extraParams = '') {
		$this->runCommand(trim("php shell.php sys controller remove home {$extraParams}"));

		foreach($this->_controllerFiles as $path) {
			if(!in_array($path, $this->_controllerStickyFiles)) {
				$this->assertNotTrue(is_file(TESTS_ROOTDIR.$path), "File '".TESTS_ROOTDIR."{$path}' was not removed.");
			}
		}
	}
	protected function removeLayout($extraParams = '') {
		$this->runCommand(trim("php shell.php sys layout remove basic {$extraParams}"));

		foreach($this->_layoutFiles as $path) {
			if(!in_array($path, $this->_layoutStickyFiles)) {
				$this->assertNotTrue(is_file(TESTS_ROOTDIR.$path), "File '".TESTS_ROOTDIR."{$path}' was not removed.");
			}
		}
	}
	abstract protected function validateBody($body);
	protected function validateHead() {
		$head = $this->byCssSelector('head');
		$this->assertTrue(boolval($head), "Unable to find the head or maybe it was not generated in the right way.");

		$title = $head->elements($this->using('css selector')->value('title'));
		$icon = $head->elements($this->using('css selector')->value('link[rel="icon"]'));

		$this->assertEquals(1, count($title), 'Unable to find a title in the head.');
		$this->assertEquals('basic', $this->title(), 'Title in the head has an unexpected value.');

		$this->assertEquals(1, count($icon), 'Unable to find a favicon setting in the head.');
		$this->assertRegExp('~/site/images/favicon\.png$~', $icon[0]->attribute('href'), 'Favicon points to an unknown image.');
	}
	// @}
}
