<?php

class DynamicMagicPropsTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCallingTheTestingAction() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->msResult), "Response property 'msResult' is not present");
		$this->assertRegExp('~^.*MyModel::methodMS.*\$this->ms->getClassName\(\).*MySingleton$~', $json->msResult, "Response property 'msResult' has an unexpected value");

		$this->assertTrue(isset($json->mysResult), "Response property 'mysResult' is not present");
		$this->assertRegExp('~^.*MyModel::methodMYS.*\$this->mys->getClassName\(\).*MySingleton$~', $json->mysResult, "Response property 'mysResult' has an unexpected value");
	}
	// @}
}
