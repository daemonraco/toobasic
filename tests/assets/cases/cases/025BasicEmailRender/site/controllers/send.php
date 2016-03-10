<?php

/**
 * @class SendController
 *
 * Accessible at '?action=send'
 */
class SendController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$helper = $this->model->email_filler;
		//
		// Build the email payload.
		$payload = new \TooBasic\EmailPayload();
		$payload->setName($helper->emailTempalte);
		$payload->setSubject($helper->subject);
		$payload->setEmails($helper->email);
		$payload->name = $helper->name;
		$payload->surname = $helper->surname;
		//
		// Rendering and sending email.
		$manager = \TooBasic\Managers\EmailsManager::Instance();
		$manager->setEmailPayload($payload);
		$manager->run();

		$this->assign('send_result', $manager->send() ? 'TRUE' : 'FALSE');

		return $this->status();
	}
}
