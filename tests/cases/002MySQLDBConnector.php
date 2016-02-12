<?php

class MySQLDBConnectorTest extends TooBasic_TestCase {
	// @}
	public function testDatabaseConnection() {
		$response = $this->getUrl('?service=test');

		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->status), 'Response has no status indicator.');
		$this->assertTrue($json->status, 'Response status indicator is false.');

		$this->assertTrue(isset($json->data), 'Response has no data section.');
		$this->assertTrue(is_object($json->data), 'Response data section is not an object.');

		$this->assertTrue(isset($json->data->executed) && isset($json->data->results), "Response data section doesn't all required fields.");

		$this->assertTrue($json->data->executed, "SQL execution didn't work.");
		$this->assertTrue(count($json->data->results) > 0, "No databases found when at least 1 should exist.");
	}
}
