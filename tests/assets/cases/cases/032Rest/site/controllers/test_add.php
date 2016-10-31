<?php

/**
 * @class TestAddController
 *
 * Accessible at '?action=test_add'
 */
class TestAddController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('formFlags', array(
			GC_FORMS_BUILDFLAG_SPACER => ""
		));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['POST'][] = 'name';
		$this->_requiredParams['POST'][] = 'age';
		$this->_requiredParams['POST'][] = 'height';
		$this->_requiredParams['POST'][] = 'conf';
		$this->_requiredParams['POST'][] = 'status';

	}
	protected function runPOST() {
		$factory = $this->representation->tests;
		$newId = $factory->create();
		if($newId) {
			$item = $factory->item($newId);

			$item->name = $this->params->post->name;
			$item->age = $this->params->post->age;
			$item->height = $this->params->post->height;
			$item->conf = $this->params->post->conf;
			$item->status = $this->params->post->status;


			$item->persist();
		} else {
			$message = 'Unable to create a new item.';
			$dberror = $factory->lastDBError();
			if($dberror) {
				$message.= " [{$dberror[0]}-{$dberror[1]}] {$dberror[2]}.";
			}
			$this->setError(HTTPERROR_INTERNAL_SERVER_ERROR, $message);
		}

		if($this->status()) {
			header('Location: '.\TooBasic\Managers\RoutesManager::Instance()->enroute(ROOTURI.'?action=tests'));
		}

		return $this->status();
	}
}
