<?php

require_once __DIR__.'/XXXSystoolLayoutAs.php';

class SystoolLayoutBasicTest extends Selenium_SystoolLayoutAsTest {
	//
	// Creation @{
	public function testCreatingAController() {
		$this->createController();
	}
	public function testCreatingTheLayout() {
		$this->createLayout('--type basic');
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
		$this->removeLayout('--type basic');
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
