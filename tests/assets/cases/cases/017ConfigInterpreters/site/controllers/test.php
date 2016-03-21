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
		$tool = $this->model->my_model;
		$this->assign('multi', $tool->cleanJSON($tool->multiConfig()));

		$this->assign('modules', $this->paths->modules());

		return $this->status();
	}
}
