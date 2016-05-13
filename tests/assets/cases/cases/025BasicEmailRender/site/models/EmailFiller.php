<?php

class EmailFillerModel extends \TooBasic\Model {
	public $name = 'John';
	public $surname = 'Doe';
	public $email = 'john.doe@someserver.com';
	public $emailTempalte = 'hello';
	public $subject = 'We miss you';
	protected function init() {
		if(isset($this->params->get->template)) {
			$this->emailTempalte = $this->params->get->template;
		}
	}
}
