<?php

require_once __DIR__.'/XXXCacheOn.php';

class CacheOnFilesTest extends CacheOnTest {
	// @}
	//
	// Test cases @{
	public function testCleaningUpFileCache() {
		$this->runCommand('rm -fv cache/filecache/*.data');
	}
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
