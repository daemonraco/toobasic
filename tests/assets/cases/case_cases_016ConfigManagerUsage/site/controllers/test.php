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
		$this->assign('single', $tool->cleanJSON($tool->singleConfig()));
		$this->assign('multi', $tool->cleanJSON($tool->multiConfig()));
		$this->assign('merge', $tool->cleanJSON($tool->mergeConfig()));

		$this->assign('modules', $this->paths->modules());

		return $this->status();
	}
}
