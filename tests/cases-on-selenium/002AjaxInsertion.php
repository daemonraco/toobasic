<?php

class Selenium_AjaxInsertionTest extends TooBasic_SeleniumTestCase {
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	public function testTestingAjaxInsertion() {
		$this->url('/?action=test&atest=subaction');

		$webdriver = $this;
		$this->waitUntil(function () use($webdriver) {
			$out = true;
			try {
				$out = $webdriver->byId('SubSection') ? true : false;
			} catch(Exception $ex) {
				$out = null;
			}

			return $out;
		}, 5000);

		$loadingSection = $this->byId('TestSection')->byTag('div');

		$this->assertNotTrue(is_null($loadingSection->attribute('data-toobasic-insert')), "Loading tag has no attribute called 'data-toobasic-insert'.");
		$this->assertNotTrue(is_null($loadingSection->attribute('data-toobasic-status')), "Loading tag has no attribute called 'data-toobasic-status'.");

		$this->assertEquals($loadingSection->attribute('data-toobasic-status'), 'loaded', "Flagged attribute 'data-toobasic-status' is not 'loaded'.");
		$this->assertRegExp('/subaction/', $loadingSection->attribute('data-toobasic-insert'), "Section didn't load the expected action.");
	}
}
