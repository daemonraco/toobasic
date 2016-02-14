<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('form', $this->params->get->form);
		$this->assign('item', [
			'name' => 'John Doe',
			'description' => 'Someone who works somewhere.',
			'age' => 36,
			'status' => 'REMOVED'
		]);
		$this->assign('formMode', $this->params->get->form_mode);

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'form';
		$this->_requiredParams['GET'][] = 'form_mode';
	}
}
