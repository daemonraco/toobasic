<?php

abstract class CacheOnTest extends TooBasic_TestCase {
	//
	// Internal properties.
	protected static $_DateFlag = '';
	protected static $_RandFlag = false;
	//
	// Set up @{
	protected $_pattern = '/DATE:(?P<date>[0-9\-\.:]+):DATE/m';
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$_RandFlag = rand(100000, 999999);
	}
	// @}
	//
	// Test cases @{
	public function testGettingACleanCall() {
		$response = $this->getUrl('?action=test_cache&rand='.self::$_RandFlag);

		$this->assertRegExp($this->_pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($this->_pattern, $response, $matches);
		self::$_DateFlag = $matches['date'];
	}
	public function testGettingTheSameCallAgain() {
		$response = $this->getUrl('?action=test_cache&rand='.self::$_RandFlag);

		$this->assertRegExp($this->_pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($this->_pattern, $response, $matches);
		$this->assertEquals(self::$_DateFlag, $matches['date'], "The cache didn't seem to keep the value");
	}
	public function testGettingTheSameCallForJsonFormat() {
		$response = $this->getUrl('?action=test_cache&format=json&rand='.self::$_RandFlag);

		$json = json_decode($response);
		$this->assertTrue(boolval($json), 'Response is not a JSON string.');

		$this->assertTrue(isset($json->date), "No field 'date' set.");

		$this->assertEquals(self::$_DateFlag, $json->date, "The cache didn't seem to keep the value");
	}
	public function testTestingTheResetCacheFlag() {
		$response = $this->getUrl('?action=test_cache&debugresetcache&rand='.self::$_RandFlag);

		$this->assertRegExp($this->_pattern, $response, "The date flag was not found");

		$matches = false;
		preg_match($this->_pattern, $response, $matches);
		$this->assertNotEquals(self::$_DateFlag, $matches['date'], "The cache was not reseted");
	}
	// @}
}
