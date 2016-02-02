<?php

class TooBasic_TestCase extends PHPUnit_Framework_TestCase {
	//
	// Set up @{
	/**
	 * @var TooBasic_AssetsManager
	 */
	protected static $_AssetsManager = false;
	protected function loadAssetsOf($path) {
		self::$_AssetsManager->loadAssetsOf($path);
	}
	public static function setUpBeforeClass() {
		if(!self::$_AssetsManager) {
			self::$_AssetsManager = new TooBasic_AssetsManager();
		}
	}
	public static function tearDownAfterClass() {
		self::$_AssetsManager->tearDown();
		self::$_AssetsManager = false;
	}
	// @}
}
