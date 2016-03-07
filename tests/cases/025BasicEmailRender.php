<?php

class BasicEmailRenderTest extends TooBasic_TestCase {
	//
	// Test cases @{
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
}
