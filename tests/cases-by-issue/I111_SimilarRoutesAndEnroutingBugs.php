<?php

/**
 * @issue 111
 * @description Similar routes and Enrouting bugs
 * @url https://github.com/daemonraco/toobasic/issues/111
 */
class I111_SimilarRoutesAndEnroutingBugsTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCallingUsingASimpleRoute() {
		$content = $this->getUrl('/route');
		$this->assertRegExp('~:CHECK:route:default:~', $content, "There route didn't work as expected.");
	}
	public function testCallingARouteUsingParameters() {
		$content = $this->getUrl('/route?value=some_value');
		$this->assertRegExp('~:CHECK:route:some_value:~', $content, "There route didn't work as expected.");
	}
	public function testCallingARouteUsingParametersInnRoute() {
		$content = $this->getUrl('/route/some_value');
		$this->assertRegExp('~:CHECK:route:some_value:~', $content, "There route didn't work as expected.");
	}
	public function testCheckingEnrouting() {
		$content = $this->getUrl('/enroute');
		$this->assertRegExp('~:LINK_1:(.*)/route:~m', $content, "There enrouting didn't work as expected.");
		$this->assertRegExp('~:LINK_2:(.*)/route/somevalue:~m', $content, "There enrouting didn't work as expected.");
	}
	// @}
}
