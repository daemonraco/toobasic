<?php

class CacheOnMemcacheTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected static $_DateFlag = '';
	protected static $_RandFlag = false;
	//
	// Set up @{
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$_RandFlag = rand(100000, 999999);
	}
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	//
	// Test cases @{
	public function testGettingACleanCall() {
		$response = $this->getUrl('?action=test&rand='.self::$_RandFlag);

		$pattern = '/DATE:(?P<date>[0-9\-\.:]+):DATE/m';
		$this->assertRegExp($pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($pattern, $response, $matches);
		self::$_DateFlag = $matches['date'];
	}
	public function testGettingTheSameCallAgain() {
		$response = $this->getUrl('?action=test&rand='.self::$_RandFlag);

		$pattern = '/DATE:(?P<date>[0-9\-\.:]+):DATE/m';
		$this->assertRegExp($pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($pattern, $response, $matches);
		$this->assertEquals(self::$_DateFlag, $matches['date'], "The cache didn't seem to keep the value");
	}
	public function testGettingTheSameCallForJsonFormat() {
		$response = $this->getUrl('?action=test&format=json&rand='.self::$_RandFlag);

		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->date), "No field 'date' set.");

		$this->assertEquals(self::$_DateFlag, $json->date, "The cache didn't seem to keep the value");
	}
	public function testTestingTheResetCacheFlag() {
		$response = $this->getUrl('?action=test&debugresetcache&rand='.self::$_RandFlag);

		$pattern = '/DATE:(?P<date>[0-9\-\.:]+):DATE/m';
		$this->assertRegExp($pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($pattern, $response, $matches);
		$this->assertNotEquals(self::$_DateFlag, $matches['date'], "The cache was not reseted");
	}
	// @}
}
