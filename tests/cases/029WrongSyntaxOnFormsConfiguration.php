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

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~/site/forms/no_form\.json~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingConfigurationWithoutFieldsList() {
		$form = 'no_fields';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~/site/forms/no_fields\.json~", $response, "Response to '{$url}' doesn't mention the broken file.");
	}
	public function testCheckingConfigurationEmptyFieldsList() {
		$form = 'empty_fields';
		$url = "?action=form_tester&form={$form}";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~JSON file at '/(.*)\.json' doesn't match the specifications~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~/site/forms/empty_fields\.json~", $response, "Response to '{$url}' doesn't mention the broken file.");
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
