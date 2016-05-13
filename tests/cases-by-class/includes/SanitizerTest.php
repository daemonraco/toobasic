<?php

class TooBasic_SanitizerTest extends TooBasic_TestCase {
	//
	// Testing Sanitizer::DirPath() @{
	public function testPathWithTooManySlashes() {
		$expected = '/some/path/script.php';
		$dir = '///some/////path/script.php';
		$this->assertEquals($expected, $this->parseByService('dir_path', $dir));
	}
	public function testAbosolutePathWithTooManySlashes() {
		$expected = 'some/path/script.php';
		$dir = 'some/////path/script.php';
		$this->assertEquals($expected, $this->parseByService('dir_path', $dir));
	}
	public function testWellFormedPath() {
		$dir = 'some/path/script.php';
		$this->assertEquals($dir, $this->parseByService('dir_path', $dir));
	}
	public function testWellFormedAbsolutePath() {
		$dir = '/some/path/script.php';
		$this->assertEquals($dir, $this->parseByService('dir_path', $dir));
	}
	public function testWellFormedPathWithEndingSlash() {
		$dir = '/some/path/';
		$this->assertEquals($dir, $this->parseByService('dir_path', $dir));
	}
	// @}
	//
	// Testing Sanitizer::UriPath() @{
	public function testUriWithTooManySlashes() {
		$expected = '/some/path/script.php?parameters=value&something=else';
		$uri = '///some/////path/script.php?parameters=value&something=else';
		$this->assertEquals($expected, $this->parseByService('uri_path', $uri));
	}
	public function testWellFormedUri() {
		$uri = '/some/path/script.php?parameters=value&something=else';
		$this->assertEquals($uri, $this->parseByService('uri_path', $uri));
	}
	public function testWellFormedSimpleUri() {
		$uri = '/some/path/';
		$this->assertEquals($uri, $this->parseByService('uri_path', $uri));
	}
	public function testWellFormedSimpleUriNoLastSlash() {
		$uri = '/some/path';
		$this->assertEquals($uri, $this->parseByService('uri_path', $uri));
	}
	public function testWellFormedUriNoScript() {
		$uri = '/some/path/?parameters=value&something=else';
		$this->assertEquals($uri, $this->parseByService('uri_path', $uri));
	}
	public function testWellFormedUriNoScriptOrLastSlash() {
		$uri = '/some/path?parameters=value&something=else';
		$this->assertEquals($uri, $this->parseByService('uri_path', $uri));
	}
	// @}
	//
	// Internal methods @{
	protected function parseByService($service, $source) {
		$parsing = $this->getJSONUrl("?service={$service}&source=".urlencode($source));

		$this->assertTrue(isset($parsing->status), "Call to service '{$service}' didn't return a status flag.");
		$this->assertTrue($parsing->status, "Call to service '{$service}' returned a bad status.");

		$this->assertTrue(isset($parsing->data->result), "Call to service '{$service}' didn't return a parsing result value.");

		return $parsing->data->result;
	}
	// @}
}
