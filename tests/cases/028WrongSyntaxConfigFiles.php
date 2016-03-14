<?php

class WrongSyntaxConfigFilesTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingBrokenRoutesSpecification() {
		$asset = '/site/configs/routes.json';
		$this->activatePreAsset($asset);

		$url = '/test';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to parse file (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	public function testCheckingBrokenTranslationSpecification() {
		$this->deactivatePreAsset('/site/configs/routes.json');
		$asset = '/site/langs/en_us.json';
		$this->activatePreAsset($asset);

		$url = '?action=test';
		$response = $this->getURL($url, false);
		debugit($response);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to parse file (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	// @}
}
