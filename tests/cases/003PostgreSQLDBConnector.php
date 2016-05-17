<?php

require_once __DIR__.'/XXXDBConnector.php';

class PostgreSQLDBConnectorTest extends DBConnectorTest {
	public function testDatabaseConnection() {
		parent::testDatabaseConnection();
	}
}
