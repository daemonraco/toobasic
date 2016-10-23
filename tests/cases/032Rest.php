<?php

// | URL                           | GET | PUT | POST | DELETE |
// |:------------------------------|:---:|:---:|:----:|:------:|
// | resource/<resource-name>      |  D  |     |  Y   |        |
// | resource/<resource-name>/<id> |  D  |  Y  |      |   Y    |
// | stats/<resource-name>         |     |     |      |        |
class RestTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testDBCreation() {
		$source = $this->getUrl('?debugdbupgrade');
	}
	public function testGettingAFullList() {
		$source = $this->getUrl('?rest=resource/tests');
		$json = json_decode($source);

		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(1, count($json), "Expected amount of items is not right.");

		$this->checkJohn($json[0]);
	}
	public function testGettingAFullListUsingRoutes() {
		$source = $this->getUrl('/rest/resource/tests');
		$json = json_decode($source);

		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(1, count($json), "Expected amount of items is not right.");

		$this->checkJohn($json[0]);
	}
	public function testGettingOneItem() {
		$source = $this->getUrl('?rest=resource/tests/1');
		$json = json_decode($source);

		$this->assertTrue(is_object($json), "Response is not as expected.");

		$this->checkJohn($json);
	}
	public function testGettingStats() {
		$source = $this->getUrl('?rest=stats/tests');
		$json = json_decode($source);

		$this->assertTrue(is_object($json), "Response is not as expected.");
		$this->assertObjectHasAttribute('count', $json);
		$this->assertEquals(1, $json->count, "Expected amount of items is not right.");
	}
	public function testGettingWrongType() {
		$source = $this->getUrl('?rest=wrongtype');
		$json = json_decode($source);

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown REST call type \'wrongtype\'~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testResourceWithWrongFactory() {
		$source = $this->getUrl('?rest=resource/wrongfactory');
		$json = json_decode($source);

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown resource \'wrongfactory\'.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testStatsWithWrongFactory() {
		$source = $this->getUrl('?rest=stats/wrongfactory');
		$json = json_decode($source);

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown resource \'wrongfactory\'.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testUnknownItem() {
		$source = $this->getUrl('?rest=resource/tests/2');
		$json = json_decode($source);

		$this->checkErrorResponse($json);

		$this->assertEquals(404, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Item \'2\' not found.~', $json->lasterror->message, "Error message is not as expected.");
	}
	// @}
	//
	// Internal methods @{
	protected function checkErrorResponse($json) {
		$this->assertTrue(is_object($json), "Response is not as expected.");

		$this->assertObjectHasAttribute('lasterror', $json, "Error response has no field 'lasterror'.");
		$this->assertObjectHasAttribute('code', $json->lasterror, "Error response last error has no field 'code'.");
		$this->assertObjectHasAttribute('message', $json->lasterror, "Error response last error has no field 'message'.");

		$this->assertObjectHasAttribute('errors', $json, "Error response has no field 'errors'.");
		$this->assertTrue(is_array($json->errors), "Error response field 'errors' is not a list.");
		foreach($json->errors as $pos => $error) {
			$at = $pos + 1;
			$this->assertObjectHasAttribute('code', $error, "Error {$at} on response has no field 'code'.");
			$this->assertObjectHasAttribute('message', $error, "Error {$at} on response has no field 'message'.");
		}
	}
	protected function checkJohn($item) {
		$this->assertObjectHasAttribute('id', $item);
		$this->assertEquals(1, $item->id);

		$this->assertObjectHasAttribute('name', $item);
		$this->assertEquals('john doe', $item->name);

		$this->assertObjectHasAttribute('age', $item);
		$this->assertEquals(38, $item->age);

		$this->assertObjectHasAttribute('height', $item);
		$this->assertEquals(1.72, $item->height);

		$this->assertObjectHasAttribute('status', $item);
		$this->assertEquals('SINGLE', $item->status);

		$this->assertObjectHasAttribute('conf', $item);
		$this->assertTrue(is_object($item->conf));

		$this->assertObjectHasAttribute('info', $item);
		$this->assertTrue(is_object($item->info));
		$this->assertObjectHasAttribute('somefield', $item->info);
		$this->assertEquals('somevalue', $item->info->somefield);
	}
	// @}
}
