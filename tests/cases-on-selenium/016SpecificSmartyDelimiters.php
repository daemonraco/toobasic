<?php

class SpecificSmartyDelimitersTest extends TooBasic_SeleniumTestCase {
	//
	// Test cases @{
	public function testAssetsCreation() {
		$this->runCommand('php shell.php sys layout create deflayout --type bootstrap');
		$this->runCommand('php shell.php sys controller create hello');

		$this->url('/');
		$this->checkCurrentSource();
		$this->checkMainAssets();
		//
		// Checking delimiters usage.
		$path = TESTS_ROOTDIR.'/site/templates/action/deflayout.html';
		$this->assertRegExp('~<!--\[.*\]-->~m', file_get_contents($path), "Specific Smarty tags '<!--[' and ']-->' are not being used.");
	}
	public function testTableAssetsCreation() {
		$this->runCommand('php shell.php sys table create person --plural people --bootstrap --column name --searchable person --name-field name --autocomplete --type mysql');
		$this->runCommand('chmod -v 0666 '.TESTS_ROOTDIR.'/cache/travis_test.sqlite3');
		//
		// Checking entries list.
		$this->url('/?action=people');
		$this->checkCurrentSource();
		$this->checkMainAssets();
		$this->assertRegExp('~<h4.*>.*people.*</h4>~m', $this->source(), "The view is not rendering correctly.");
		//
		// Checking delimiters usage.
		$path = TESTS_ROOTDIR.'/site/templates/action/people.html';
		$this->assertRegExp('~<!--\[.*\]-->~m', file_get_contents($path), "Specific Smarty tags '<!--[' and ']-->' are not being used.");
	}
	// @}
	//
	// Protected methods.
	protected function checkMainAssets() {
		//
		// Checking the inclusion of JS assets.
		$jsAsset = $this->elements($this->using('css selector')->value('script[data-toobasic="true"][src*="/script.js"]'));
		$this->assertEquals(1, count($jsAsset), "Layout is not loading the JS asset 'script.js'");
		//
		// Checking the inclusion of CSS assets.
		$cssAsset = $this->elements($this->using('css selector')->value('link[data-toobasic="true"][href*="/style.css"][rel="stylesheet"]'));
		$this->assertEquals(1, count($cssAsset), "Layout is not loading the CSS asset 'style.css'");
	}
}
