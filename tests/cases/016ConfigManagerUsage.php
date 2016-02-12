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
	// @}
}
