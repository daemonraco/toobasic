<?php

class WrongSyntaxOnRoutesConfigurationTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testNoActionOrServiceConfigured() {
		$this->activatePreAsset('/modules/no_action_or_service/configs/routes.json');

		$url = "/unknown_action";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Wrong route specification in(.*). A route should have either an 'action' or a 'service'.~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Wrong route specification in(.*)/modules/no_action_or_service/configs/routes.json~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset('/modules/no_action_or_service/configs/routes.json');
	}
	public function testNoRouteFieldConfigured() {
		$this->deactivatePreAsset('/modules/no_action_or_service/configs/routes.json');
		$this->activatePreAsset('/modules/no_route_field/configs/routes.json');

		$url = "/unknown_action";
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Wrong route specification in(.*). No field 'route' given.~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~Wrong route specification in(.*)/modules/no_route_field/configs/routes.json~", $response, "Response to '{$url}' doesn't mention the broken file.");

		$this->deactivatePreAsset('/modules/no_route_field/configs/routes.json');
	}
	// @}
}
