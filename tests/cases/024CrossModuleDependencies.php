<?php

class CrossModuleDependenciesTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingAProperLoading() {
		$this->getJSONUrl('?action=test&format=json');
	}
	public function testCheckingIncompatibleModules() {
		$this->activatePreAsset('/modules/wrong_dependant_module/manifest.json');

		$url = '?action=test&format=json';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Required version '7.3' for module 'Basic Module' .found version '1.8'. .module: wrong_dependant_module.~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testCheckingIncompatibleModulesButInstalledMode() {
		$this->activatePreAsset('/site/installed_config.php');
		$this->getJSONUrl('?action=test&format=json');
	}
	// @}
}
