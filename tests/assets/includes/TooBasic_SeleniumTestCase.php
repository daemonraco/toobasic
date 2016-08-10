<?php

class TooBasic_SeleniumTestCase extends PHPUnit_Extensions_Selenium2TestCase {
	//
	// Internal properties.
	protected $_autocleanDynamicAssets = false;
	//
	// Set up @{
	/**
	 * @var TooBasic_AssetsManager
	 */
	protected static $_AssetsManager = false;
	public static function setUpBeforeClass() {
		if(!self::$_AssetsManager) {
			self::$_AssetsManager = new TooBasic_AssetsManager();
		}
	}
	public function setUp() {
		$this->loadAssetsOf();
		//
		// Driver?
		$driver = defined('TRAVISCI_PHPUNIT_DRIVER') ? TRAVISCI_PHPUNIT_DRIVER : 'firefox';
		//
		// Selenium settings.
		$this->setHost('localhost');
		$this->setPort(4444);
		$this->setBrowser($driver);

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
	protected function activatePreAsset($subpath) {
		self::$_AssetsManager->activatePreAsset($subpath);
	}
	protected function checkCurrentSource() {
		$this->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $this->source(), "Response to '{$this->url()}' seems to have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $this->source(), "Response to '{$this->url()}' seems to have a PHP error.");
	}
	protected function clearEmails($assertResult = true, $assertReturnValue = true, $promptResult = true) {
		return TooBasic_Helper::ClearEmails($this, $assertResult, $assertReturnValue, $promptResult);
	}
	protected function deactivateAllPreAsset() {
		if($this->_autocleanDynamicAssets) {
			self::$_AssetsManager->deactivateAllPreAsset();
		}
	}
	protected function deactivatePreAsset($subpath) {
		self::$_AssetsManager->deactivatePreAsset($subpath);
	}
	public function getEmail($index, $assertIt = true) {
		return TooBasic_Helper::GetEmail($this, $index, $assertIt);
	}
	protected function getBasicUrl($subUrl, $assertIt = true) {
		return TooBasic_Helper::GetUrl($this, $subUrl, $assertIt);
	}
	protected function getJSONUrl($subUrl, $assertIt = true) {
		return TooBasic_Helper::GetJSONUrl($this, $subUrl, $assertIt);
	}
	protected function loadAssetsOf($path = false) {
		$filePath = $path;
		if($path === false) {
			$reflector = new ReflectionClass(get_called_class());
			$filePath = $reflector->getFileName();
		}

		self::$_AssetsManager->loadAssetsOf($filePath);
	}
	protected function runCommand($command, $assertResult = true, $assertReturnValue = true, $promptResult = true) {
		return TooBasic_Helper::RunCommand($this, $command, $assertResult, $assertReturnValue, $promptResult);
	}
	// @}
}
