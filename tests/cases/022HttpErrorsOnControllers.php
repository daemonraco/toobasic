<?php

class HttpErrorsOnControllersTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingHttp400() {
		$response = $this->getUrl('?action=test&code=400');

		$this->assertRegExp('~400 - Bad Request</title>~m', $response, "Response doesn't metion the 400 error in the title.");
		$this->assertRegExp('~<h1>400 - Bad Request</h1>~m', $response, "Response doesn't metion the 400 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingHttp401() {
		$response = $this->getUrl('?action=test&code=401');

		$this->assertRegExp('~401 - Unauthorized</title>~m', $response, "Response doesn't metion the 401 error in the title.");
		$this->assertRegExp('~<h1>401 - Unauthorized</h1>~m', $response, "Response doesn't metion the 401 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingHttp403() {
		$response = $this->getUrl('?action=test&code=403');

		$this->assertRegExp('~403 - Access Denided</title>~m', $response, "Response doesn't metion the 403 error in the title.");
		$this->assertRegExp('~<h1>403 - Access Denided</h1>~m', $response, "Response doesn't metion the 403 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingHttp404() {
		$response = $this->getUrl('?action=test&code=404');

		$this->assertRegExp('~404 - Not found</title>~m', $response, "Response doesn't metion the 404 error in the title.");
		$this->assertRegExp('~<h1>404 - Not found</h1>~m', $response, "Response doesn't metion the 404 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingHttp500() {
		$response = $this->getUrl('?action=test&code=500');

		$this->assertRegExp('~500 - Internal Server Error</title>~m', $response, "Response doesn't metion the 500 error in the title.");
		$this->assertRegExp('~<h1>500 - Internal Server Error</h1>~m', $response, "Response doesn't metion the 500 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingHttp501() {
		$response = $this->getUrl('?action=test&code=501');

		$this->assertRegExp('~501 - Not Implemented</title>~m', $response, "Response doesn't metion the 501 error in the title.");
		$this->assertRegExp('~<h1>501 - Not Implemented</h1>~m', $response, "Response doesn't metion the 501 error.");
		$this->assertRegExp('~<p>CONTROLLED ERROR</p>~m', $response, "Response doesn't have the error description.");
	}
	public function testCheckingAnUnknownHttpErrorCode() {
		$subUrl = '?action=test&code=600';
		$response = $this->getUrl($subUrl, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$subUrl}'.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$subUrl}' seems to have a PHP error.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$subUrl}' doesn't have a TooBasic exception.");
		$this->assertRegExp("~<p>There's no page/controller set to handle the HTTP error '600'</p>~m", $response, "Response doesn't have the error description mentioning the HTTP-600 code.");
	}
	// @}
}
