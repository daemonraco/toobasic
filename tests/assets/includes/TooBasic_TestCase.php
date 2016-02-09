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
	//
	// Internal methods @{
	protected function getUrl($subUrl, $assertExceptions = true) {
		$url = TRAVISCI_URL_SCHEME.'://localhost';
		$url.= TRAVISCI_URL_PORT ? ':'.TRAVISCI_URL_PORT : '';
		$url.= (TRAVISCI_URI ? TRAVISCI_URI : '').'/';
		$url.= $subUrl;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		curl_close($ch);

		if($assertExceptions) {
			$this->assertNotRegExp('/TooBasic.([a-zA-Z]*)Exception/m', $response, "Response to '{$subUrl}' seems to have a TooBasic exception.");
			$this->assertNotRegExp('/Fatal error:/m', $response, "Response to '{$subUrl}' seems to have a PHP error.");
		}

		return $response;
	}
	// @}
}
