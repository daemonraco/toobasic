<?php

class WrongSyntaxConfigFilesTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected $_autocleanDynamicAssets = true;
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
	}
	public function testCheckingBrokenTranslationSpecification() {
		$asset = '/site/langs/en_us.json';
		$this->activatePreAsset($asset);

		$url = '?action=test';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to parse file (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingBrokenSapiReaderSpecification() {
		$asset = '/site/sapis/broken_api.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_sapis';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to load path (.*){$asset}~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingBrokenSapiReportSpecification() {
		$asset = '/site/sapis/reports/broken_report.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_report';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Path '(.*){$asset}' is not a valid JSON~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingBrokenFormsSpecification() {
		$asset = '/site/forms/broken_form.json';
		$this->activatePreAsset($asset);

		$url = '?action=test_forms';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to load form 'broken_form'.~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingBrokenDbSpecification() {
		$asset = '/site/db/broken_table.json';
		$this->activatePreAsset($asset);

		$url = '?action=test';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON spec at '(.*){$asset}' is broken~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	// @}
}
