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
		$this->assign('msResult', $this->model->my_model->methodMS());
		$this->assign('mysResult', $this->model->my_model->methodMYS());

		return $this->status();
	}
}
