<?php

class TooBasic_SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {
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
	public function setUp() {
		//
		// Selenium settings.
		$this->setHost('localhost');
		$this->setPort(4444);
		$this->setBrowser('firefox');

		$url = TRAVISCI_URL_SCHEME.'://localhost';
		$url.= TRAVISCI_URL_PORT ? ':'.TRAVISCI_URL_PORT : '';
		$url.= (TRAVISCI_URI ? TRAVISCI_URI : '').'/';

		$this->setBrowserUrl($url);
	}
	public static function tearDownAfterClass() {
		self::$_AssetsManager->tearDown();
		self::$_AssetsManager = false;
	}
	// @}
	//
	// Internal methods @{
	protected function checkCurrentSource() {
		$this->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $this->source(), "Response to '{$this->url()}' seems to have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $this->source(), "Response to '{$this->url()}' seems to have a PHP error.");
	}
	protected function getBasicUrl($subUrl, $assertIt = true) {
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
