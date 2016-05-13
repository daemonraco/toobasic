<?php

require_once __DIR__.'/XXXSystoolLayoutAs.php';

class SystoolLayoutBootstrapTest extends Selenium_SystoolLayoutAsTest {
	//
	// Internal properties.
	protected $_layoutFiles = [
		'/site/configs/config_http.php',
		'/site/controllers/basic.php',
		'/site/controllers/basic_nav.php',
		'/site/scripts/script.js',
		'/site/styles/style.css',
		'/site/templates/action/basic.html',
		'/site/templates/action/basic_nav.html'
	];
	//
	// Creation @{
	public function testCreatingAController() {
		$this->createController();
	}
	public function testCreatingTheLayout() {
		$this->createLayout('--type bootstrap');
	}
	// @}
	//
	// Checks @{
	public function testCheckingPageRender() {
		$this->url('?action=home');
		$this->checkCurrentSource();

		$body = $this->byCssSelector('body.container.Action_home');

		$this->validateHead();
		$this->validateBody($body);
	}
	// @}
	//
	// Removal @{
	public function testRemovingTheLayout() {
		$this->removeLayout('--type bootstrap');
	}
	public function testRemovingTheController() {
		$this->removeController();
	}
	// @}
	//
	// Creation (fluid) @{
	public function testCreatingAControllerUsingFluid() {
		$this->createController();
	}
	public function testCreatingTheLayoutUsingFluid() {
		$this->createLayout('--type bootstrap --fluid');
	}
	// @}
	//
	// Checks (fluid) @{
	public function testCheckingPageRenderUsingFluid() {
		$this->url('?action=home');
		$this->checkCurrentSource();

		$body = $this->byCssSelector('body.Action_home');

		$this->validateHead();
		$this->validateBody($body);
	}
	// @}
	//
	// Removal (fluid) @{
	public function testRemovingTheLayoutUsingFluid() {
		$this->removeLayout('--type bootstrap --fluid');
	}
	public function testRemovingTheControllerUsingFluid() {
		$this->removeController();
	}
	// @}
	//
	// Internal methods @{
	protected function validateBody($body) {
		$this->assertTrue(boolval($body), 'Unable to find the body or maybe it was not generated in the right way.');

		$header = $body->elements($this->using('css selector')->value('header'));
		$footer = $body->elements($this->using('css selector')->value('footer'));
		$contents = $body->elements($this->using('css selector')->value('div#MainContents'));

		$this->assertEquals(1, count($header), 'Unable to find a header section in the body.');
		$this->assertEquals(1, count($footer), 'Unable to find a footer section in the body.');
		$this->assertEquals(1, count($contents), 'Unable to find a contents section in the body.');

		$this->assertRegExp('~(.*)home(.*)HomeController(.*)~m', $contents[0]->text(), 'Main content is not the expected controller.');

		$nav = $header[0]->elements($this->using('css selector')->value('nav.navbar.navbar-default'));
		$this->assertEquals(1, count($nav), "Header doesn't have a nav-bar.");
		$navContainer = $nav[0]->elements($this->using('css selector')->value('div.container-fluid'));
		$this->assertEquals(1, count($navContainer), "Nav-bar doesn't have a fluid container.");
		$brandLink = $nav[0]->elements($this->using('css selector')->value('a.navbar-brand'));
		$this->assertEquals(1, count($brandLink), "Nav-bar doesn't brand link.");
		$this->assertEquals('basic', $brandLink[0]->text(), 'Nav-bar brand link has a wrong value.');
		$this->assertRegExp('~#$~', $brandLink[0]->attribute('href'), 'Nav-bar brand link points to an unexpected URL.');
	}
	// @}
}
