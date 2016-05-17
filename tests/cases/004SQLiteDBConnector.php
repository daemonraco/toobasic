<?php

require_once __DIR__.'/XXXDBConnector.php';

class SQLiteDBConnectorTest extends DBConnectorTest {
	public function testDatabaseConnection() {
		$this->_assertCounts = false;
		parent::testDatabaseConnection();
	}
}
