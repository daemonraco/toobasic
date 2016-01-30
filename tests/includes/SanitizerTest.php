<?php

class SanitizerTest extends PHPUnit_Framework_TestCase {
	public function testTooManySlashes() {
		$expected = '/some/path/script.php?parameters=value&something=else';
		$uri = '///some/////path/script.php?parameters=value&something=else';
		$this->assertEquals($expected, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormmedUri() {
		$uri = '/some/path/script.php?parameters=value&something=else';
		$this->assertEquals($uri.'l', TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormmedSimpleUri() {
		$uri = '/some/path/';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormmedSimpleUriNoLastSlash() {
		$uri = '/some/path';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormmedUriNoScript() {
		$uri = '/some/path/?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
	public function testWellFormmedUriNoScriptOrLastSlash() {
		$uri = '/some/path?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
}
