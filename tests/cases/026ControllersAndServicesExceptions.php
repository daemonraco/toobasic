<?php

class ControllersAndServicesExceptionsTest extends TooBasic_TestCase {
	//
	// Controller test cases @{
	public function testRequestingAWrongController() {
		$response = $this->getUrl('?action=unknown_action');
		$this->assertRegExp('~404 - Not found~m', $response, "The response doesn't inform about a HTTP-404 error.");
		$this->assertRegExp('~Unknown action .unknown_action.~m', $response, "The response doesn't mention the failing controller.");
	}
	public function testRequestingAWrongClassController() {
		$url = '?action=wrong_class';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~Class \'WrongClassController\' is not defined.~m', $response, "The response doesn't inform about an undefined class.");
	}
	public function testRequestingAControllerWithoutView() {
		$url = '?action=no_view';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $response, "Response to '{$url}' doesn't have a Smarty exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~Unable to load template \'(.*)action/no_view.html\'~m', $response, "The response doesn't inform about a template error.");
	}
	public function testRequestingAControllerWithWrongParams() {
		$response = $this->getUrl('?action=wrong_params');

		$this->assertRegExp('~400 - Bad Request~m', $response, "The response doesn't inform about a HTTP-400 error.");
		$this->assertRegExp('~Parameter \'param\' is not set \(GET\)~m', $response, "The response doesn't inform the wrong parameter.");
	}
	// @}
	//
	// Service test cases @{
	public function testRequestingAWrongService() {
		$json = $this->getJSONUrl('?service=unknown_service');
		$this->checkJSONErrorResponse($json);

		$this->assertEquals(3, $json->error->code, "Error information field 'code' has an unexpected value.");
		$this->assertRegExp('~unknown_service~m', $json->error->message, "Error information field 'message' doesn't mention service 'unknown_service'.");
	}
	public function testRequestingAWrongClassService() {
		$url = '?service=wrong_class';
		$response = $this->getUrl($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp('~Class \'WrongClassService\' is not defined~m', $response, "The response doesn't inform about an undefined class.");
	}
	public function testRequestingAServiceWithWrongParams() {
		$json = $this->getJSONUrl('?service=wrong_params');
		$this->checkJSONErrorResponse($json);

		$this->assertEquals(400, $json->error->code, "Error information field 'code' has an unexpected value.");
		$this->assertRegExp('~Parameter \'param\' is not set \\(GET\\)~m', $json->error->message, "Error information field 'message' doesn't mention the wrong parameter.");
	}
	// @}
	//
	// Internal methods @{
	protected function checkJSONErrorResponse($json) {
		$this->assertTrue(isset($json->status), "Response doesn't have a field called 'status'.");
		$this->assertNotTrue($json->status, "Response status is ok and it shouldn't.");

		$this->assertTrue(isset($json->error), "Response doesn't have a field called 'error'.");
		$this->assertTrue(is_object($json->error), "Response field 'error' is not an object.");

		$this->assertTrue(isset($json->errors), "Response doesn't have a field called 'errors'.");
		$this->assertTrue(is_array($json->errors), "Response field 'errors' is not a list.");

		$this->assertTrue(isset($json->error->code), "Error information doesn't have a field called 'code'.");
		$this->assertTrue(isset($json->error->message), "Error information doesn't have a field called 'message'.");
	}
	// @}
}
