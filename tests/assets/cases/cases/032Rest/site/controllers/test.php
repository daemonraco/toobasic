<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false; # \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Protected methods.
	protected function basicRun() {
		$factory = $this->representation->tests;
		$item = $factory->item($this->params->get->id);
		if($item) {
			$this->assign('test', $item->toArray());
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "Unknown test with id '{$this->params->get->id}'");
		}

		$this->assign('formFlags', array(
			GC_FORMS_BUILDFLAG_SPACER => ""
		));

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_requiredParams['GET'][] = 'id';
	}
}
