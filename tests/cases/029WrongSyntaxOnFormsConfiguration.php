<?php

class WrongSyntaxOnFormsConfigurationTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingConfigurationWithoutFieldForm() {
		$form = 'no_form';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Wrong form specification, unable to find path '///form'~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Wrong form specification(.*)form '{$form}'~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingConfigurationWithoutFieldsList() {
		$form = 'no_fields';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Wrong form specification, unable to find path '///form/fields' or maybe empty~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Wrong form specification(.*)form '{$form}'~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingConfigurationEmptyFieldsList() {
		$form = 'empty_fields';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Wrong form specification, unable to find path '///form/fields' or maybe empty~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Wrong form specification(.*)form '{$form}'~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingBrokenEnumConfiguration() {
		$form = 'broken_enum';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Type 'enum' at path '///form/fields/somefield' has no values~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Type 'enum' at path '///form/fields/somefield' has no values .form '{$form}'~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	// @}
}
