<?php

require_once __DIR__.'/XXXDBConnector.php';

class MySQLDBConnectorTest extends DBConnectorTest {
	public function testDatabaseConnection() {
		parent::testDatabaseConnection();
	}
}
