<?php

class TooBasic_TestCase extends PHPUnit_Framework_TestCase {
	//
	// Set up @{
	/**
	 * @var TooBasic_AssetsManager
	 */
	protected static $_AssetsManager = false;
	protected function loadAssetsOf($path = false) {
		$filePath = $path;
		if($path === false) {
			$reflector = new ReflectionClass(get_called_class());
			$filePath = $reflector->getFileName();
		}

		self::$_AssetsManager->loadAssetsOf($filePath);
	}
	public static function setUpBeforeClass() {
		if(!self::$_AssetsManager) {
			self::$_AssetsManager = new TooBasic_AssetsManager();
		}
	}
	public function setUp() {
		$this->loadAssetsOf();
		parent::setUp();
	}
	public static function tearDownAfterClass() {
		self::$_AssetsManager->tearDown();
		self::$_AssetsManager = false;
	}
	// @}
	//
	// Internal methods @{
	protected function getUrl($subUrl, $assertIt = true) {
		return TooBasic_Helper::GetUrl($this, $subUrl, $assertIt);
	}
	protected function getJSONUrl($subUrl, $assertIt = true) {
		return TooBasic_Helper::GetJSONUrl($this, $subUrl, $assertIt);
	}
	protected function runCommand($command, $assertResult = true, $assertReturnValue = true, $promptResult = true) {
		return TooBasic_Helper::RunCommand($this, $command, $assertResult, $assertReturnValue, $promptResult);
	}
	// @}
}
