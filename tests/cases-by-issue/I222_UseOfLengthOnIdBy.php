<?php

/**
 * @issue 222
 * @brief Use of length on idBy
 * @url https://github.com/daemonraco/toobasic/issues/222
 */
class I222_UseOfLengthOnIdByTest extends TooBasic_TestCase {
	//
	// Internal properties @{
	protected $_items = [
		1 => 'Canada',
		2 => 'Argentina',
		3 => 'Angola',
		4 => 'Mexico',
		5 => 'Japan',
		6 => 'Java'
	];
	// @}
	//
	// Test cases @{
	public function testCreateDB() {
		$this->getUrl('');
	}
	public function testRetrivingAll() {
		$json = $this->getJSONUrl('?service=test&mode=list');

		$this->assertTrue(isset($json->status), "Response is not as expected (path: <json>/status).");
		$this->assertTrue($json->status, "Response status is not ok.");

		$this->assertTrue(isset($json->data), "Response is not as expected (path: <json>/data).");
		$this->assertTrue(isset($json->data->items), "Response is not as expected (path: <json>/data/items).");

		$this->checkAllItems($json->data->items);
	}
	public function testRetrivingAllUsingStream() {
		$json = $this->getJSONUrl('?service=test&mode=list_stream');

		$this->assertTrue(isset($json->status), "Response is not as expected (path: <json>/status).");
		$this->assertTrue($json->status, "Response status is not ok.");

		$this->assertTrue(isset($json->data), "Response is not as expected (path: <json>/data).");
		$this->assertTrue(isset($json->data->items), "Response is not as expected (path: <json>/data/items).");

		$this->checkAllItems($json->data->items);
	}
	public function testRetrivingJapanId() {
		$json = $this->getJSONUrl('?service=test&mode=idby&name=Japan');

		$this->assertTrue(isset($json->status), "Response is not as expected (path: <json>/status).");
		$this->assertTrue($json->status, "Response status is not ok.");

		$this->assertTrue(isset($json->data), "Response is not as expected (path: <json>/data).");
		$this->assertTrue(isset($json->data->id), "Response is not as expected (path: <json>/data/id).");
		$this->assertEquals(5, $json->data->id, "Response has the wrong ID.");
	}
	public function testRetrivingJapanByName() {
		$json = $this->getJSONUrl('?service=test&mode=byname&name=Japan');

		$this->assertTrue(isset($json->status), "Response is not as expected (path: <json>/status).");
		$this->assertTrue($json->status, "Response status is not ok.");

		$this->assertTrue(isset($json->data), "Response is not as expected (path: <json>/data).");
		$this->assertTrue(isset($json->data->item), "Response is not as expected (path: <json>/data/item).");
		$this->assertTrue(isset($json->data->item->id), "Response is not as expected (path: <json>/data/item/id).");
		$this->assertTrue(isset($json->data->item->name), "Response is not as expected (path: <json>/data/item/name).");
		$this->assertEquals(5, $json->data->item->id, "Response has the wrong ID.");
		$this->assertEquals('Japan', $json->data->item->name, "Response has the wrong name.");
	}
	// @}
	//
	// Internal methods @{
	protected function checkAllItems($items) {
		$this->assertEquals(6, count($items), "Expected amount of items is not right.");
		foreach($items as $item) {
			$this->assertTrue(isset($this->_items[$item->id]), "Item with id '{$item->id}' was not expected");
		}
	}
	// @}
}
