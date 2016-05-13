<?php

class FormTesterController extends \TooBasic\Controller {
	protected $_cached = false;
	protected function basicRun() {
		$this->assign('formName', $this->params->get->form);

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'form';
	}
}
