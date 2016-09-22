<?php

class WrongSyntaxOnSApiReaderConfigurationTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected $_autocleanDynamicAssets = true;
	//
	// Broken file cases @{
	public function testLoadingAnUnknownConfiguration() {
		$url = "?action=test_api&api=unknown";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to find configuration 'unknown'.~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingABrokenConfiguration() {
		$this->activatePreAsset('/site/sapis/broken.json');

		$url = "?action=test_api&api=broken";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~(.*)/site/sapis/broken.json(.*)is not a valid JSON~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithWrongJsonSyntax() {
		$this->activatePreAsset('/site/sapis/wrong_syntax.json');

		$url = "?action=test_api&api=wrong_syntax";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~(.*)/site/sapis/wrong_syntax.json(.*)is not a valid JSON \((.*)\)~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~(.*)/site/sapis/wrong_syntax.json(.*)is not a valid JSON~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	// @}
	//
	// Empty configurations cases @{
	public function testLoadingAnEmptyConfiguration() {
		$this->activatePreAsset('/site/sapis/empty.json');

		$url = "?action=test_api&api=empty";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
	//
	// Lost main fields cases @{
	public function testLoadingAConfigurationWithNoServicesField() {
		$this->activatePreAsset('/site/sapis/no_services.json');

		$url = "?action=test_api&api=no_services";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Configuration field 'services' is not present.~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithNoUrlField() {
		$this->activatePreAsset('/site/sapis/no_url.json');

		$url = "?action=test_api&api=no_url";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithNoDescriptionField() {
		$this->activatePreAsset('/site/sapis/no_description.json');

		$url = "?action=test_api&api=no_description";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
	//
	// Field 'services' cases @{
	public function testLoadingAConfigurationWithWrongServicesFormat() {
		$this->activatePreAsset('/site/sapis/services_as_string.json');

		$url = "?action=test_api&api=services_as_string";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Configuration field 'services' is not an object~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithEmptyServicesList() {
		$this->activatePreAsset('/site/sapis/empty_services.json');

		$url = "?action=test_api&api=empty_services";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Configuration field 'services' is empty~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithAServicesWithoutUri() {
		$this->activatePreAsset('/site/sapis/no_uri_services.json');

		$url = "?action=test_api&api=no_uri_services";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testLoadingAConfigurationWithAServicesWithWrongSendParams() {
		$this->activatePreAsset('/site/sapis/wrong_send_params.json');

		$url = "?action=test_api&api=wrong_send_params";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
	//
	// Header checks @{
	public function testLoadingAConfigurationWithWrongHedearsField() {
		$this->activatePreAsset('/site/sapis/wrong_headers.json');

		$url = "?action=test_api&api=wrong_headers";
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
}
