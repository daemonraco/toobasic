<?php

class TooBasic_SanitizerTest extends PHPUnit_Framework_TestCase {
	//
	// Testing Sanitizer::DirPath() @{
	public function testPathWithTooManySlashes() {
		$expected = '/some/path/script.php';
		$dir = '///some/////path/script.php';
		$this->assertEquals($expected, TooBasic\Sanitizer::DirPath($dir));
	}
	public function testAbosolutePathWithTooManySlashes() {
		$expected = 'some/path/script.php';
		$dir = 'some/////path/script.php';
		$this->assertEquals($expected, TooBasic\Sanitizer::DirPath($dir));
	}
	public function testWellFormedPath() {
		$dir = 'some/path/script.php';
		$this->assertEquals($dir, TooBasic\Sanitizer::DirPath($dir));
	}
	public function testWellFormedAbsolutePath() {
		$dir = '/some/path/script.php';
		$this->assertEquals($dir, TooBasic\Sanitizer::DirPath($dir));
	}
	public function testWellFormedPathWithEndingSlash() {
		$dir = '/some/path/';
		$this->assertEquals($dir, TooBasic\Sanitizer::DirPath($dir));
	}
	// @}
	//
	// Testing Sanitizer::UriPath() @{
	public function testUriWithTooManySlashes() {
		$expected = '/some/path/script.php?parameters=value&something=else';
		$uri = '///some/////path/script.php?parameters=value&something=else';
		$this->assertEquals($expected, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormedUri() {
		$uri = '/some/path/script.php?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormedSimpleUri() {
		$uri = '/some/path/';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormedSimpleUriNoLastSlash() {
		$uri = '/some/path';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormedUriNoScript() {
		$uri = '/some/path/?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormedUriNoScriptOrLastSlash() {
		$uri = '/some/path?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	// @}
}
