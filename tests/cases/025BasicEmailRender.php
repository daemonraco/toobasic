<?php

class BasicEmailRenderTest extends TooBasic_TestCase {
	//
	// Happy tests :D @{
	public function testCheckingRenderUsingDebugs() {
		$source = $this->getUrl('?debugemail=hello');
		//
		// Checking sections.
		$this->assertRegExp('~data-ingnored-attr..LAYOUTMARKER~m', $source, 'Unable to find the layout marker.');
		$this->assertRegExp('~data-ingnored-attr..CONTROLLERMARKER~m', $source, 'Unable to find the controller marker.');
		//
		// Checking assignment values.
		$this->assertRegExp('~NAME:SOMENAME:~m', $source, 'Unable to find name assignment.');
		$this->assertRegExp('~SURNAME:SOMESURNAME:~m', $source, 'Unable to find sur-name assignment.');
	}
	public function testCheckingRenderAndSend() {
		//
		// Sending email.
		$response = $this->getUrl('?action=send');
		$this->assertRegExp('~:SENDRESULT:TRUE:~m', $response, "Email was not delivered.");
		//
		// Loading sent email.
		$source = $this->getEmail(1);
		//
		// Checking sections.
		$this->assertRegExp('~data-ingnored-attr..LAYOUTMARKER~m', $source, 'Unable to find the layout marker.');
		$this->assertRegExp('~data-ingnored-attr..CONTROLLERMARKER~m', $source, 'Unable to find the controller marker.');
		//
		// Checking assignment values.
		$this->assertRegExp('~NAME:John:~m', $source, 'Unable to find name assignment.');
		$this->assertRegExp('~SURNAME:Doe:~m', $source, 'Unable to find sur-name assignment.');
		//
		// Checking headers.
		$this->assertRegExp('~To: john.doe@someserver.com~m', $source, "Wrong recipient.");
		$this->assertRegExp('~Subject: We miss you~m', $source, "Wrong subject.");
		$this->assertRegExp('~From: mailer@mysite.com~m', $source, "Wrong origin email.");
		$this->assertRegExp('~Reply-To: no-replay@mysite.com~m', $source, "Wrong reply-to email.");
		$this->assertRegExp('~X-PowerdBy: TooBasic~m', $source, "Wrong header 'X-PowerdBy'.");
	}
	// @}
	//
	// Unknown mail tests @{
	public function testRenderUnknownTempalteUsingDebugs() {
		$url = '?debugemail=unknown';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to find email 'unknown'.~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testRenderUnknownTempalteAndSend() {
		$url = '?action=send&template=unknown';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to find email 'unknown'.~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
	//
	// Wrong mail layout tests @{
	public function testRenderWrongLayoutUsingDebugs() {
		$url = '?debugemail=wrong_layout';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to find email layout 'unknown_layout'~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testRenderWrongLayoutAndSend() {
		$url = '?action=send&template=wrong_layout';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Unable to find email layout 'unknown_layout'~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
	//
	// Wrong mail class tests @{
	public function testRenderWrongClassUsingDebugs() {
		$url = '?debugemail=wrong_class';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Class 'WrongClassEmail' is not defined~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~File '(.*)/site/emails/wrong_class.php' doesn't seem to load the right object.~", $response, "Error doesn't mention the included file.");
	}
	public function testRenderWrongClassAndSend() {
		$url = '?action=send&template=wrong_class';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' doesn't have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");

		$this->assertRegExp("~Class 'WrongClassEmail' is not defined~", $response, "Response to '{$url}' doesn't mention the error.");
		$this->assertRegExp("~File '(.*)/site/emails/wrong_class.php' doesn't seem to load the right object.~", $response, "Error doesn't mention the included file.");
	}
	// @}
	//
	// Wrong mail class tests @{
	public function testRenderNoViewEmailUsingDebugs() {
		$url = '?debugemail=no_view';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' seems to have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");
		$this->assertRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $response, "Response to '{$url}' doesn't have a Smarty exception.");

		$this->assertRegExp("~Unable to load template file 'email/no_view.html'~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	public function testRenderNoViewEmailAndSend() {
		$url = '?action=send&template=no_view';
		$response = $this->getURL($url, false);

		$this->assertTrue(boolval($response), "No response obtained for '{$url}'.");
		$this->assertNotRegExp(ASSERTION_PATTERN_TOOBASIC_EXCEPTION, $response, "Response to '{$url}' seems to have a TooBasic exception.");
		$this->assertNotRegExp(ASSERTION_PATTERN_PHP_ERROR, $response, "Response to '{$url}' seems to have a PHP error.");
		$this->assertRegExp(ASSERTION_PATTERN_SMARTY_EXCEPTION, $response, "Response to '{$url}' doesn't have a Smarty exception.");

		$this->assertRegExp("~Unable to load template file 'email/no_view.html'~", $response, "Response to '{$url}' doesn't mention the error.");
	}
	// @}
}
