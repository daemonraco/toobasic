<?php

/**
 * @class TestEditController
 *
 * Accessible at '?action=test_edit'
 */
class TestEditController extends \TooBasic\Controller {
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
		$this->_requiredParams['POST'][] = 'name';
		$this->_requiredParams['POST'][] = 'age';
		$this->_requiredParams['POST'][] = 'height';
		$this->_requiredParams['POST'][] = 'conf';
		$this->_requiredParams['POST'][] = 'status';

	}
	protected function runPOST() {
		$factory = $this->representation->tests;
		$item = $factory->item($this->params->get->id);
		if($item) {
			$item->name = $this->params->post->name;
			$item->age = $this->params->post->age;
			$item->height = $this->params->post->height;
			$item->conf = $this->params->post->conf;
			$item->status = $this->params->post->status;


			$item->persist();
		} else {
			$this->setError(HTTPERROR_BAD_REQUEST, "Unknown test with id '{$this->params->get->id}'");
		}

		if($this->status()) {
			header('Location: '.\TooBasic\Managers\RoutesManager::Instance()->enroute(ROOTURI.'?action=tests'));
		}

		return $this->status();
	}
}
