<?php

class FieldFiltersTest extends TooBasic_TestCase {
	protected $_itemsBefore = [
		[
			'id' => 1,
			'props' => [],
			'status' => true,
			'indexed' => false
		], [
			'id' => 2,
			'props' => [
				'hello' => 'world!'
			],
			'status' => false,
			'indexed' => false
		]
	];
	protected $_itemsAfter = [
		[
			'id' => 1,
			'props' => [],
			'status' => true,
			'indexed' => false
		], [
			'id' => 2,
			'props' => [
				'hello' => 'world!',
				'newProp' => 10.3
			],
			'status' => true,
			'indexed' => false
		]
	];
	//
	// Test cases @{
	public function testInitalValues() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->items), "Response field 'items' is not present.");
		$this->assertTrue(is_array($json->items), "Field 'items' is not a list.");
		$this->assertEquals(count($this->_itemsBefore), count($json->items), "Field 'items' has an unexpected amount of items.");
		$this->compareItems($this->_itemsBefore, $json->items);
	}
	public function testValuesAfterEdition() {
		$json = $this->getJSONUrl('?action=edit&format=json');

		$this->assertTrue(isset($json->before), "Response field 'before' is not present.");
		$this->assertTrue(is_array($json->before), "Field 'before' is not a list.");
		$this->assertEquals(count($this->_itemsBefore), count($json->before), "Field 'before' has an unexpected amount of items.");
		$this->compareItems($this->_itemsBefore, $json->before);

		$this->assertTrue(isset($json->after), "Response field 'after' is not present.");
		$this->assertTrue(is_array($json->after), "Field 'after' is not a list.");
		$this->assertEquals(count($this->_itemsAfter), count($json->after), "Field 'after' has an unexpected amount of items.");
		$this->compareItems($this->_itemsAfter, $json->after);
	}
	// @}
	//
	// Internal methods @{
	protected function compareItems($expected, $list) {
		foreach($expected as $pos => $item) {
			foreach($item as $k => $v) {
				$this->assertTrue(isset($list[$pos]->{$k}), "Field '{$k}' in item '{$pos}' is not present.");
				if($k == 'props') {
					foreach($v as $pk => $pv) {
						$this->assertTrue(isset($list[$pos]->{$k}), "Field '{$k}->{$pk}' in item '{$pos}' is not present.");
						$this->assertEquals($pv, $list[$pos]->{$k}->{$pk}, "Field '{$k}->{$pk}' in item '{$pos}' has an unexpected value.");
					}
				} else {
					$this->assertEquals($v, $list[$pos]->{$k}, "Field '{$k}' in item '{$pos}' has an unexpected value.");
				}
			}
		}
	}
	// @}
}
