<?php

class SystoolServiceTest extends TooBasic_TestCase {
	//
	// Internals.
	protected $_serviceName = 'test';
	protected $_moduleName = 'mymodule';
	//
	// Test cases @{
	public function testCreatingANewService() {
		$this->runCommand("php shell.php sys service create {$this->_serviceName} --param value --module {$this->_moduleName}");
	}
	public function testAccessingTheService() {
		$transaction = rand(0, 1000);
		$json = $this->getJSONUrl("?service={$this->_serviceName}&value=10&transaction={$transaction}");

		$this->assertTrue(isset($json->status), "Response has no status.");
		$this->assertTrue($json->status, "Response status is not OK.");

		$this->assertTrue(isset($json->data), "Response has no data section.");
		$this->assertTrue(is_object($json->data), "Response data section is not an object.");

		$this->assertTrue(isset($json->transaction), "Response has no transaction id.");
		$this->assertEquals($transaction, $json->transaction, "Response's transaction id is not as expected.");
	}
	public function testAccessingTheServiceUsingRoutes() {
		$transaction = rand(0, 1000);
		$json = $this->getJSONUrl("/srv/{$this->_serviceName}/10?transaction={$transaction}");

		$this->assertTrue(isset($json->status), "Response has no status.");
		$this->assertTrue($json->status, "Response status is not OK.");

		$this->assertTrue(isset($json->data), "Response has no data section.");
		$this->assertTrue(is_object($json->data), "Response data section is not an object.");

		$this->assertTrue(isset($json->transaction), "Response has no transaction id.");
		$this->assertEquals($transaction, $json->transaction, "Response's transaction id is not as expected.");
	}
	public function testAccessingTheServiceWithoutParameters() {
		$transaction = rand(0, 1000);
		$json = $this->getJSONUrl("?service={$this->_serviceName}&transaction={$transaction}");

		$this->assertTrue(isset($json->status), "Response has no status.");
		$this->assertNotTrue($json->status, "Response status is not OK.");

		$this->assertTrue(isset($json->error), "Response has no error section.");
		$this->assertTrue(is_object($json->error), "Response error section is not an object.");

		$this->assertTrue(isset($json->errors), "Response has no errors section.");
		$this->assertTrue(is_array($json->errors), "Response errors section is not a list.");

		$this->assertTrue(isset($json->transaction), "Response has no transaction id.");
		$this->assertEquals($transaction, $json->transaction, "Response's transaction id is not as expected.");
	}
	public function testRemovingTheService() {
		$this->runCommand("php shell.php sys service remove {$this->_serviceName} --module {$this->_moduleName}");
	}
	// @}
}
