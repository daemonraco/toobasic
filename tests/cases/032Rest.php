<?php

// | URL                           | GET | PUT | POST | DELETE |
// |:------------------------------|:---:|:---:|:----:|:------:|
// | resource/<resource-name>      |  D  |     |  D   |        |
// | resource/<resource-name>/<id> |  D  |  D  |      |   D    |
// | stats/<resource-name>         |  D  |     |      |        |
// | search/<resource-name>        |  D  |     |      |        |
class RestTest extends TooBasic_TestCase {
	//
	// Properties.
	protected $_jane = '{"name":"jane doe","age":"27","height":"1.53","conf":{"somefield":"somevalue"},"info":null,"status":"MARRIED"}';
	//
	// Test cases @{
	public function testDBCreation() {
		$source = $this->getUrl('?debugdbupgrade');
	}
	public function testGettingAFullList() {
		$json = $this->getJSONUrl('?rest=resource/tests');

		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(1, count($json), "Expected amount of items is not right.");

		$this->checkJohn($json[0]);
	}
	public function testGettingAFullListUsingRoutes() {
		$json = $this->getJSONUrl('/rest/resource/tests');

		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(1, count($json), "Expected amount of items is not right.");

		$this->checkJohn($json[0]);
	}
	public function testGettingOneItem() {
		$json = $this->getJSONUrl('?rest=resource/tests/1');

		$this->assertTrue(is_object($json), "Response is not as expected.");

		$this->checkJohn($json);
	}
	public function testGettingStats() {
		$json = $this->getJSONUrl('?rest=stats/tests');

		$this->assertTrue(is_object($json), "Response is not as expected.");
		$this->assertObjectHasAttribute('count', $json);
		$this->assertEquals(1, $json->count, "Expected amount of items is not right.");
	}
	public function testGettingWrongType() {
		$json = $this->getJSONUrl('?rest=wrongtype');

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown REST call type \'wrongtype\'~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testResourceWithWrongFactory() {
		$json = $this->getJSONUrl('?rest=resource/wrongfactory');

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown resource \'wrongfactory\'.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testStatsWithWrongFactory() {
		$json = $this->getJSONUrl('?rest=stats/wrongfactory');

		$this->checkErrorResponse($json);

		$this->assertEquals(400, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Unknown resource \'wrongfactory\'.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testUnknownItem() {
		$json = $this->getJSONUrl('?rest=resource/tests/2');

		$this->checkErrorResponse($json);

		$this->assertEquals(404, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Item \'2\' not found.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testCreateNewItem() {
		$postBody = json_decode($this->_jane);
		$json = $this->sendJSONUrl('?rest=resource/tests', 'POST', $postBody);
		$this->checkJane($json, 2);

		$json = $this->getJSONUrl('?rest=resource/tests/2');
		$this->checkJane($json, 2);

		$json = $this->getJSONUrl('?rest=stats/tests');
		$this->assertTrue(is_object($json), "Response is not as expected.");
		$this->assertObjectHasAttribute('count', $json);
		$this->assertEquals(2, $json->count, "Expected amount of items is not right.");
	}
	public function testUpdateItem() {
		$postBody = json_decode($this->_jane);
		$json = $this->sendJSONUrl('?rest=resource/tests/1', 'PUT', $postBody);
		$this->checkJane($json, 1);

		$json = $this->getJSONUrl('?rest=resource/tests/1');
		$this->checkJane($json, 1);
	}
	public function testDeleteItem() {
		$json = $this->sendJSONUrl('?rest=resource/tests/2', 'DELETE');

		$this->assertObjectHasAttribute('status', $json, "Response has no field 'status'.");
		$this->assertTrue($json->status, "Response is not as expected.");

		$json = $this->getJSONUrl('?rest=resource/tests/2');
		$this->checkErrorResponse($json);
		$this->assertEquals(404, $json->lasterror->code, "Error code is not as expected.");
		$this->assertRegExp('~Item \'2\' not found.~', $json->lasterror->message, "Error message is not as expected.");
	}
	public function testLimitedList() {
		$this->activatePreAsset('/modules/wrongfactory/db/tests-data.json');
		$this->getUrl('?debugdbupgrade');
		//
		// Full list.
		$json = $this->getJSONUrl('?rest=resource/tests');
		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(7, count($json), "Expected amount of items is not right.");
		$this->checkNames($json, ['jane doe', 'apple', 'banana', 'black berry', 'lime', 'mellon', 'watermellon']);
		//
		// First three.
		$json = $this->getJSONUrl('?rest=resource/tests&limit=3');
		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(3, count($json), "Expected amount of items is not right.");
		$this->checkNames($json, ['jane doe', 'apple', 'banana']);
		//
		// Three items with offset.
		$json = $this->getJSONUrl('?rest=resource/tests&limit=3&offset=3');
		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(3, count($json), "Expected amount of items is not right.");
		$this->checkNames($json, ['black berry', 'lime', 'mellon']);
	}
	public function testSearchForSomething() {
		$json = $this->getJSONUrl('?rest=search/tests/name/jane%20doe');
		$this->assertTrue(is_array($json), "Response is not as expected.");
		$this->assertEquals(1, count($json), "Expected amount of items is not right.");
		$this->checkJane($json[0], 1);
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
	protected function checkNames($json, $names) {
		foreach($names as $at => $name) {
			$this->assertEquals($name, $json[$at]->name, "Item at position {$at} has the wrong name.");
		}
	}
	protected function checkJane($item, $id) {
		$this->assertObjectHasAttribute('id', $item);
		$this->assertEquals($id, $item->id);

		$this->assertObjectHasAttribute('name', $item);
		$this->assertEquals('jane doe', $item->name);

		$this->assertObjectHasAttribute('age', $item);
		$this->assertEquals(27, $item->age);

		$this->assertObjectHasAttribute('height', $item);
		$this->assertEquals(1.53, $item->height);

		$this->assertObjectHasAttribute('status', $item);
		$this->assertEquals('MARRIED', $item->status);

		$this->assertObjectHasAttribute('conf', $item);
		$this->assertTrue(is_object($item->conf));
		$this->assertObjectHasAttribute('somefield', $item->conf);
		$this->assertEquals('somevalue', $item->conf->somefield);

		$this->assertObjectHasAttribute('info', $item);
		$this->assertTrue(is_object($item->info));
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
