<?php

class TooBasic_ParamsTest extends TooBasic_TestCase {
	//
	// Basic parameter sets @{
	public function testGetParameters() {
		$this->myParameterSetTest('get');
		$this->myParameterSetTest('get');
	}
	public function testPostParameters() {
		$this->myParameterSetTest('post');
	}
	public function testHeadersParameters() {
		$this->myParameterSetTest('headers');
	}
	public function testCookiesParameters() {
		$this->myParameterSetTest('cookie');
	}
	public function testServerParameters() {
		$this->myParameterSetTest('server');
	}
	public function testEnvironmentParameters() {
		$this->myParameterSetTest('env');
	}
	public function testShellOptionsParameters() {
		$this->myParameterSetTest('opt');
	}
	public function testInternalUsageParameters() {
		$this->myParameterSetTest('internal');
	}
	// @}
	//
	// Basic method @{
	public function testDebugsDetectors() {
		$debugs = $this->getJSONUrl('?service=debugs');

		$this->assertTrue(isset($debugs->status), "Call to service 'debugs' didn't return a status flag.");
		$this->assertTrue($debugs->status, "Call to service 'debugs' returned a bad status.");

		$this->assertTrue(isset($debugs->data->has_debugs_is_bool), "Call to service 'debugs' didn't return a result field called 'has_debugs_is_bool'.");
		$this->assertTrue($debugs->data->has_debugs_is_bool, "Call to service 'debugs' didn't return TRUE on field 'has_debugs_is_bool'.");

		$this->assertTrue(isset($debugs->data->debugs_is_array), "Call to service 'debugs' didn't return a result field called 'debugs_is_array'.");
		$this->assertTrue($debugs->data->debugs_is_array, "Call to service 'debugs' didn't return TRUE on field 'debugs_is_array'.");
	}
	// @}
	//
	// Parameters usage @{
	public function testSettingParameterValue() {
		$results = $this->getJSONUrl('?service=internal&source='.urlencode('somevalue'));

		$this->assertTrue(isset($results->status), "Call to service 'internal' didn't return a status flag.");
		$this->assertTrue($results->status, "Call to service 'internal' returned a bad status.");

		$this->assertTrue(isset($results->data->result), "Call to service 'internal' didn't return a parsing result value.");
		$this->assertEquals('somevalue', $results->data->result, "Call to service 'internal' returned a wrong result.");
	}
	// @}
	//
	// Internal methods @{
	public function myParameterSetTest($parameter) {
		$result = $this->getJSONUrl('?service=set_tester&param_name='.urlencode($parameter));

		$this->assertTrue(isset($result->status), "Call to service 'set_tester' didn't return a status flag.");
		$this->assertTrue($result->status, "Call to service 'set_tester' returned a bad status.");
		//
		// Parameter existence.
		$this->assertTrue(isset($result->data->is_object), "Call to service 'set_tester' didn't return a result field called 'is_object'.");
		$this->assertTrue($result->data->is_object, "Call to service 'set_tester' didn't return TRUE on field 'is_object'.");
		//
		// Parameter contents.
		$this->assertTrue(isset($result->data->all_is_array), "Call to service 'set_tester' didn't return a result field called 'all_is_array'.");
		$this->assertTrue($result->data->all_is_array, "Call to service 'set_tester' didn't return TRUE on field 'all_is_array'.");
		//
		// In camel-case.
		$this->assertTrue(isset($result->data->capital_is_object), "Call to service 'set_tester' didn't return a result field called 'capital_is_object'.");
		$this->assertTrue($result->data->capital_is_object, "Call to service 'set_tester' didn't return TRUE on field 'capital_is_object'.");
		//
		// In upper-case.
		$this->assertTrue(isset($result->data->upper_is_object), "Call to service 'set_tester' didn't return a result field called 'upper_is_object'.");
		$this->assertTrue($result->data->upper_is_object, "Call to service 'set_tester' didn't return TRUE on field 'upper_is_object'.");
	}
	// @}
}
