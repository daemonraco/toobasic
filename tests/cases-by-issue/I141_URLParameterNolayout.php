<?php

/**
 * @issue 141
 * @brief URL Parameter 'nolayout'
 * @url https://github.com/daemonraco/toobasic/issues/141
 */
class I141_URLParameterNolayoutTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testCallingAControllerWithoutLayout() {
		$result = $this->getUrl('?action=test&nolayout');
		$this->assertNotRegExp('~MAIN CONTENT~m', $result, 'Layout content found.');
		$this->assertRegExp('~CONTROLLER CONTENT~m', $result, 'Controller content is not as expected.');
	}
	public function testCheckingDebugsInformation() {
		$result = $this->getUrl('?debugdebugs');
		$this->assertNotRegExp('~debugnolayout~m', $result, 'Debug flag to disable layouts is still listed.');
	}
	public function testCallingAControllerWithoutLayoutAndDisabledDebug() {
		$this->activatePreAsset('/site/configs/nodebugs.php');

		$result = $this->getUrl('?action=test&nolayout');
		$this->assertNotRegExp('~MAIN CONTENT~m', $result, 'Layout content found.');
		$this->assertRegExp('~CONTROLLER CONTENT~m', $result, 'Controller content is not as expected.');
	}
	// @}
}
