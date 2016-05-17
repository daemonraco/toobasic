<?php

require_once __DIR__.'/XXXSystoolLayoutAs.php';

class SystoolLayoutTableTest extends Selenium_SystoolLayoutAsTest {
	//
	// Creation @{
	public function testCreatingAController() {
		$this->createController();
	}
	public function testCreatingTheLayout() {
		$this->createLayout('--type table');
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
		$this->removeLayout('--type table');
	}
	public function testRemovingTheController() {
		$this->removeController();
	}
	// @}
	//
	// Internal methods @{
	protected function validateBody($body) {
		$this->assertTrue(boolval($body), 'Unable to find the body or maybe it was not generated in the right way.');

		$contents = $body->elements($this->using('css selector')->value('td#MainContents'));

		$this->assertEquals(1, count($contents), 'Unable to find a contents section in the body.');
		$this->assertRegExp('~(.*)home(.*)HomeController(.*)~m', $contents[0]->text(), 'Main content is not the expected controller.');

		$table = $body->elements($this->using('css selector')->value('table#MainTable'));
		$title = $body->elements($this->using('css selector')->value('td#Titlebar'));
		$menu = $body->elements($this->using('css selector')->value('td#MainMenu'));

		$this->assertEquals(1, count($table), 'Unable to find a main layout table.');
		$this->assertEquals(1, count($title), 'Unable to find a main layout\'s title cell.');
		$this->assertEquals(1, count($menu), 'Unable to find a main layout\'s menu cell.');

		$this->assertEquals('basic', $title[0]->text(), 'Title section has an unexpected value.');
		$this->assertEquals(2, $title[0]->attribute('colspan'), 'Title section has an unexpected width.');
	}
	// @}
}
