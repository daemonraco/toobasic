<?php

class DBSpecsMultiDataDefinitionTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testRequestingTheInitialData() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->count), "Response field 'count' is not present.");
		$this->assertEquals(0, $json->count, "Response field 'count' has an unexpected value.");

		$this->assertTrue(isset($json->items), "Response field 'items' is not present.");
		$this->assertTrue(is_array($json->items), "Response field 'items' is not a list.");
		$this->assertEquals(0, count($json->items), "Response field 'items' has an unexpected amount of items.");
	}
	public function testAddingSomeDataFileAndChecking() {
		$this->activatePreAsset('/site/db/entries.1.json');
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->count), "Response field 'count' is not present.");
		$this->assertEquals(1, $json->count, "Response field 'count' has an unexpected value.");

		$this->assertTrue(isset($json->items), "Response field 'items' is not present.");
		$this->assertTrue(is_array($json->items), "Response field 'items' is not a list.");
		$this->assertEquals(1, count($json->items), "Response field 'items' has an unexpected amount of items.");

		$this->assertTrue(is_object($json->items[0]), "First item is not an object.");
		$this->assertTrue(isset($json->items[0]->id), "First item doesn't have an id.");
		$this->assertEquals(1, $json->items[0]->id, "First item's id has an unexpected value.");
		$this->assertTrue(isset($json->items[0]->name), "First item doesn't have a name.");
		$this->assertEquals('Entry 1', $json->items[0]->name, "First item's name has an unexpected value.");
	}
	public function testAddingAnotherDataFilesAndChecking() {
		$this->activatePreAsset('/site/db/entries.2.json');
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->count), "Response field 'count' is not present.");
		$this->assertEquals(2, $json->count, "Response field 'count' has an unexpected value.");

		$this->assertTrue(isset($json->items), "Response field 'items' is not present.");
		$this->assertTrue(is_array($json->items), "Response field 'items' is not a list.");
		$this->assertEquals(2, count($json->items), "Response field 'items' has an unexpected amount of items.");

		foreach(['First', 'Second'] as $idx => $name) {
			$id = $idx + 1;
			$this->assertTrue(is_object($json->items[$idx]), "{$name} item is not an object.");
			$this->assertTrue(isset($json->items[$idx]->id), "{$name} item doesn't have an id.");
			$this->assertEquals($id, $json->items[$idx]->id, "{$name} item's id has an unexpected value.");
			$this->assertTrue(isset($json->items[$idx]->name), "{$name} item doesn't have a name.");
			$this->assertEquals("Entry {$id}", $json->items[$idx]->name, "{$name} item's name has an unexpected value.");
		}
	}
	// @}
}
