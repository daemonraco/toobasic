<?php

require_once __DIR__.'/XXXCacheOn.php';

class CacheOnMySQLDatabaseTest extends CacheOnTest {
	// @}
	//
	// Test cases @{
	public function testGettingACleanCall() {
		parent::testGettingACleanCall();
	}
	public function testGettingTheSameCallAgain() {
		parent::testGettingTheSameCallAgain();
	}
	public function testGettingTheSameCallForJsonFormat() {
		parent::testGettingTheSameCallForJsonFormat();
	}
	public function testTestingTheResetCacheFlag() {
		parent::testTestingTheResetCacheFlag();
	}
	// @}
}
