<?php

class SendmailTest extends TooBasic_TestCase {
	//
	// Test cases @{
	public function testSendmail() {
		$response = $this->getUrl("sendmail.php");
		debugit([
			'$response' => $response,
			'mail' => file_get_contents('/tmp/fake-mailbox/message_1.eml')
		]);
	}
	// @}
}
