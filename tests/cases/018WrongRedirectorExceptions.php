<?php

class WrongRedirectorExceptionsTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testBasicCall() {
		$subUrl = '?action=test';
		$response = $this->getUrl($subUrl, false);
		$this->assertTrue(boolval($response), "No response obtained.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$subUrl}' seems to have a PHP error.");

		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$subUrl}' doesn't have a TooBasic exception.");
		$this->assertRegExp("/Redirection code 'wrong-destination' is not configured/m", $response, "Response exception doesn't mention the wrong redirector.");
	}
	// @}
}
