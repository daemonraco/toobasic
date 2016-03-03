<?php

/**
 * @issue 116
 */
class CustomControllerExportsMethodsTest extends TooBasic_TestCase {
	//
	// Test cases.
	public function testUsingACustomFunction() {
		$response = $this->getUrl('?action=test');
		$this->assertRegExp('/^VALUE:somevalue:secondvalue:$/', $response, 'The result is not as expected.');
	}
	// @}
}
