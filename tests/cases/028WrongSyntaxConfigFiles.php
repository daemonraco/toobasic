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

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to parse file (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	public function testCheckingBrokenSapiReaderSpecification() {
		$this->deactivatePreAsset('/site/langs/en_us.json');
		$asset = '/site/sapis/broken_api.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_sapis';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to load path (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	public function testCheckingBrokenSapiReportSpecification() {
		$this->deactivatePreAsset('/site/sapis/broken_api.json');
		$asset = '/site/sapis/reports/broken_report.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_report';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Path '(.*){$asset}' is not a valid JSON~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	public function testCheckingBrokenFormsSpecification() {
		$this->deactivatePreAsset('/site/sapis/reports/broken_report.json');
		$asset = '/site/forms/broken_form.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_forms';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to load form 'broken_form'.~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	public function testCheckingBrokenDbSpecification() {
		$this->deactivatePreAsset('/site/forms/broken_form.json');
		$asset = '/site/db/broken_table.json';
		$this->activatePreAsset($asset);

		$url = '?action=test';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON spec at '(.*){$asset}' is broken~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset($asset);
	}
	// @}
}
