<?php

abstract class MultiTablesSelectsOnTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testChekingMultiTableSelect() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->masters), "Response doesn't have a field called 'masters'.");
		$this->assertTrue(is_array($json->masters), "Response field 'masters' is not a list.");

		$this->assertTrue(isset($json->children), "Response doesn't have a field called 'children'.");
		$this->assertTrue(is_array($json->children), "Response field 'children' is not a list.");

		$this->assertTrue(isset($json->entries), "Response doesn't have a field called 'entries'.");
		$this->assertTrue(is_array($json->entries), "Response field 'entries' is not a list.");

		$this->assertTrue(isset($json->search), "Response doesn't have a field called 'search'.");
		$this->assertTrue(is_array($json->search), "Response field 'search' is not a list.");

		$this->assertEquals(2, count($json->masters), "Response field 'masters' has an unexpected amount of items.");
		$this->assertEquals(4, count($json->children), "Response field 'children' has an unexpected amount of items.");
		$this->assertEquals(3, count($json->entries), "Response field 'entries' has an unexpected amount of items.");
		$this->assertEquals(1, count($json->search), "Response field 'search' has an unexpected amount of items.");
	}
	// @}
}
