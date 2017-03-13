<?php

/**
 * @class TestDeleteController
 *
 * Accessible at '?action=test_delete'
 */
class TestDeleteController extends \TooBasic\Controller {
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
		$this->_requiredParams['POST'][] = 'id';
	}
	protected function runPOST() {
		$factory = $this->representation->tests;
		$item = $factory->item($this->params->post->id);
		if($item) {
			if(!$item->remove()) {
				$this->setError(HTTPERROR_INTERNAL_SERVER_ERROR, "Unable to remove test with id '{$this->params->post->id}'");
				$ok = false;
			}
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "Unknown test with id '{$this->params->post->id}'");
		}

		if($this->status()) {
			header('Location: '.\TooBasic\Managers\RoutesManager::Instance()->enroute(ROOTURI.'?action=tests'));
		}

		return $this->status();
	}
}
