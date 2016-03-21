<?php

class LayoutExceptionsTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected $_autocleanDynamicAssets = true;
	//
	// Layout as default cases @{
	public function testUsingAnUnknownDefaultLayout() {
		$this->activatePreAsset('/site/unknown_layout.php');
		$response = $this->getUrl('?action=layout_tester');

		$this->assertRegExp('~404 - Not found~m', $response, "The response doesn't inform about a HTTP-404 error.");
		$this->assertRegExp('~Unknown action .unknown_layout.~m', $response, "The response doesn't mention the failing controller.");
	}
	public function testUsingADefaultLayoutWithWrongClass() {
		$this->activatePreAsset('/site/broken_layout.php');

		$url = '?action=layout_tester';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~BrokenLayoutController(.*)not defined~m', $response, "The response doesn't inform about an undefined class.");
	}
	public function testUsingADefaultLayoutWithWrongStatus() {
		$this->activatePreAsset('/site/failing_layout.php');

		$url = '?action=layout_tester';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~500 - Internal Server Error~m', $response, "The response doesn't inform about a HTTP-500 error.");
		$this->assertRegExp('~FORCED INTERNAL ERROR~m', $response, "The response doesn't have the expected error message.");
	}
	// @}
	//
	// Layout as specific cases@{
	public function testUsingAnUnknownFixedLayout() {
		$response = $this->getUrl('?action=fixed_unknown_layout');

		$this->assertRegExp('~404 - Not found~m', $response, "The response doesn't inform about a HTTP-404 error.");
		$this->assertRegExp('~Unknown action .unknown_layout.~m', $response, "The response doesn't mention the failing controller.");
	}
	public function testUsingAFixedLayoutWithWrongClass() {
		$url = '?action=fixed_broken_layout';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~BrokenLayoutController(.*)not defined~m', $response, "The response doesn't inform about an undefined class.");
	}
	public function testUsingAFixedLayoutWithWrongStatus() {
		$url = '?action=fixed_failing_layout';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~500 - Internal Server Error~m', $response, "The response doesn't inform about a HTTP-500 error.");
		$this->assertRegExp('~FORCED INTERNAL ERROR~m', $response, "The response doesn't have the expected error message.");
	}
	// @}
}
