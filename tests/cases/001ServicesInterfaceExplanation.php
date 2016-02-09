<?php

class ServicesInterfaceExplanationTest extends TooBasic_TestCase {
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	//
	// Test cases.
	public function testRequestingAFullInterfaceExplanation() {
		$response = $this->getUrl('?explaininterface');

		$this->assertTrue(boolval($response), "No response obtained.");

		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->status), 'Response has no status indicator.');
		$this->assertTrue($json->status, 'Response status indicator is false.');

		$this->assertTrue(isset($json->services), 'Response has no services list.');
		$this->assertTrue(is_array($json->services), 'Services list is not actually a list.');
	}
	public function testCallingASpecificService() {
		$response = $this->getUrl('?service=hello_world');

		$this->assertTrue(boolval($response), "No response obtained.");
		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->status), 'Response has no status indicator.');
		$this->assertTrue($json->status, 'Response status indicator is false.');

		$this->assertTrue(isset($json->data), 'Response has no data section.');
		$this->assertTrue(is_object($json->data), 'Services data section is not an object.');

		$this->assertTrue(isset($json->data->hello), 'Response is not as expected.');
		$this->assertEquals($json->data->hello, 'world', "Response value 'hello' has an unexpected value.");
	}
	public function testExplainingASingleServiceInterface() {
		$response = $this->getUrl('?service=hello_world&explaininterface');

		$this->assertTrue(boolval($response), "No response obtained.");
		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->status), 'Response has no status indicator.');
		$this->assertTrue($json->status, 'Response status indicator is false.');
		$this->assertTrue(isset($json->interface), 'Response has no interface structure.');
		$this->assertTrue(isset($json->error), 'Response has no error indicator.');
		$this->assertTrue(isset($json->errors), 'Response has no errors list.');

		$this->assertTrue(isset($json->interface->name), 'Interface structure has no name.');
		$this->assertTrue(isset($json->interface->cached), 'Interface structure has no cache indicator.');
		$this->assertTrue(isset($json->interface->methods), 'Interface structure has no methods list.');
		$this->assertTrue(is_array($json->interface->methods), 'Interface methods list is not an actual list.');
		$this->assertTrue(isset($json->interface->required_params), 'Interface structure has no required parameters list.');
		$this->assertTrue(is_array($json->interface->required_params), 'Interface required parameters list is not an actual list.');
		$this->assertTrue(isset($json->interface->cache_params), 'Interface structure has no cache parameters list.');
		$this->assertTrue(is_object($json->interface->cache_params), 'Interface cache parameters list is not an object.');
		$this->assertTrue(isset($json->interface->CORS), 'Interface structure has no CORS policy indicators.');
		$this->assertTrue(is_object($json->interface->CORS), 'Interface CORS policy indicators is not an object.');
	}
	// @}
}
