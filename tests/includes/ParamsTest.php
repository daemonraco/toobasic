<?php

class TooBasic_ParamsTest extends TooBasic_TestCase {
	//
	// Set up @{
	protected $_params = false;
	public function setUp() {
		$this->_params = \TooBasic\Params::Instance();
	}
	// @}
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
		$this->assertTrue(is_bool($this->_params->hasDebugs()));
		$this->assertTrue(is_array($this->_params->debugs()));
	}
	// @}
	//
	// Parameters usage @{
	public function testSettingParameterValue() {
		$this->_params->internal->someparam = 'somevalue';
		$this->assertEquals('somevalue', $this->_params->internal->someparam);
	}
	// @}
	//
	// Internal methods @{
	public function myParameterSetTest($parameter) {
		//
		// Parameter existence.
		$parameter = strtolower($parameter);
		$this->assertTrue(is_object($this->_params->{$parameter}));
		//
		// Parameter contents.
		$this->assertTrue(is_array($this->_params->{$parameter}->all()));
		//
		// In camel-case.
		$parameter = ucwords($parameter);
		$this->assertTrue(is_object($this->_params->{$parameter}));
		//
		// In upper-case.
		$parameter = strtoupper($parameter);
		$this->assertTrue(is_object($this->_params->{$parameter}));
	}
	// @}
}
