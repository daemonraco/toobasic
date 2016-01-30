<?php

use TooBasic\Paths;

class SanitizerTest extends PHPUnit_Framework_TestCase {
	public function testWellFormUriGetUntouched() {
		$uri = '/some/path/script.php?parameters=value&something=else';
		$this->assertEquals($uri, TooBasic\Sanitizer::UriPath($uri));
	}
}
