<?php

/**
 * @issue 116
 */
class CustomControllerExportsMethodsTest extends TooBasic_TestCase {
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	//
	// Test cases.
	public function testUsingACustomFunction() {
		$response = $this->getUrl('?action=test');
		$this->assertRegExp('/^VALUE:somevalue:secondvalue:$/', $response, 'The result is not as expected.');
	}
	// @}
}
