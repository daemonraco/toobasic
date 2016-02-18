<?php

class ConfigManagerUsageTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCheckingConfigLoadInSingleMode() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->single), "Response doesn't have a field called 'single'.");
		$this->assertTrue(is_string($json->single), "Response field 'single' is not a string.");

		$results = json_decode($json->single);
		$this->assertTrue(is_object($results), "Response field 'single' is not an encoded object.");

		$expected = [
			'property' => [
				'value' => 'B',
				'location' => 'mod1'
			],
			'mod1prop' => [
				'value' => 'B',
				'location' => 'mod1'
			]
		];
		foreach(array_keys($expected) as $field) {
			foreach($expected[$field] as $subField => $subFieldValue) {
				$this->assertTrue(isset($results->{$field}), "Config doesn't have a field called '{$field}'.");
				$this->assertTrue(is_object($results->{$field}), "Config field '{$field}' is not an object.");
				$this->assertTrue(isset($results->{$field}->{$subField}), "Config field '{$field}' doesn't have a sub-field called '{$subField}'.");

				$this->assertEquals($subFieldValue, $results->{$field}->{$subField}, "Config field '{$field}->{$subField}' has an unexpected value.");
			}
		}
		foreach(['mod2prop', 'siteprop'] as $field) {
			$this->assertNotTrue(isset($results->{$field}), "Config has a field called '{$field}' and it shouldn't.");
		}
	}
	public function testCheckingConfigLoadInMultiMode() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->multi), "Response doesn't have a field called 'multi'.");
		$this->assertTrue(is_string($json->multi), "Response field 'multi' is not a string.");

		$results = json_decode($json->multi);
		$this->assertTrue(is_object($results), "Response field 'multi' is not an encoded object.");

		$expected = [
			'property' => [
				'value' => 'A',
				'location' => 'site'
			],
			'mod1prop' => [
				'value' => 'B',
				'location' => 'mod1'
			],
			'mod2prop' => [
				'value' => 'C',
				'location' => 'mod2'
			],
			'siteprop' => [
				'value' => 'A',
				'location' => 'site'
			]
		];
		foreach(array_keys($expected) as $field) {
			foreach($expected[$field] as $subField => $subFieldValue) {
				$this->assertTrue(isset($results->{$field}), "Config doesn't have a field called '{$field}'.");
				$this->assertTrue(is_object($results->{$field}), "Config field '{$field}' is not an object.");
				$this->assertTrue(isset($results->{$field}->{$subField}), "Config field '{$field}' doesn't have a sub-field called '{$subField}'.");

				$this->assertEquals($subFieldValue, $results->{$field}->{$subField}, "Config field '{$field}->{$subField}' has an unexpected value.");
			}
		}
	}
	public function testCheckingConfigLoadInMergeMode() {
		$json = $this->getJSONUrl('?action=test&format=json');

		$this->assertTrue(isset($json->merge), "Response doesn't have a field called 'merge'.");
		$this->assertTrue(is_string($json->merge), "Response field 'merge' is not a string.");

		$results = json_decode($json->merge);
		$this->assertTrue(is_object($results), "Response field 'merge' is not an encoded object.");
		//
		// Basic field checks.
		$this->assertTrue(isset($results->common), "JSON object doesn't have a field called 'common'.");
		$this->assertTrue(isset($results->list), "JSON object doesn't have a field called 'list'.");
		$this->assertTrue(isset($results->settings), "JSON object doesn't have a field called 'settings'.");

		$this->assertTrue(is_string($results->common), "JSON field 'common' is not a string.");
		$this->assertTrue(is_array($results->list), "JSON field 'list' is not an array.");
		$this->assertTrue(is_object($results->settings), "JSON field 'settings' is not an object.");

		$this->assertEquals('site', $results->common, "JSON field 'common' has an unexpected value.");
		//
		// Field 'list'.
		$this->assertEquals(6, count($results->list), "JSON field 'list' has an unexpected amount of items.");
		$this->assertEquals(5, count(array_unique($results->list)), "JSON field 'list' has an unexpected amount of unique items.");
		$items = [
			'value_1',
			'value_2',
			'value_3',
			'value_4',
			'value_5'
		];
		foreach($items as $value) {
			$this->assertTrue(in_array($value, $results->list), "JSON field 'list' doesn't have the item '{$value}'.");
		}
		//
		// Field 'settings'.
		$this->assertEquals(5, count(get_object_vars($results->settings)), "JSON field 'settings' has an unexpected amount of properties.");
		$items = [
			'prop_1' => 'site',
			'prop_2' => 'site',
			'prop_3' => 'mod1',
			'prop_4' => 'mod1',
			'prop_5' => 'mod2'
		];
		foreach($items as $key => $value) {
			$this->assertTrue(isset($results->settings->{$key}), "JSON field 'settings' doesn't have the property '{$key}'.");
			$this->assertEquals($value, $results->settings->{$key}, "JSON field 'settings' property '{$key}' has an unexpected value.");
		}
	}
	// @}
}
