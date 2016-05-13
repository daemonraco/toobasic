<?php

class SetTesterService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$paramName = strtolower(urldecode($this->params->get->param_name));
		//
		// Parameter existence.
		$this->assign('is_object', is_object($this->params->{$paramName}));
		//
		// Parameter contents.
		$this->assign('all_is_array', is_array($this->params->{$paramName}->all()));
		//
		// In camel-case.
		$paramName = ucwords($paramName);
		$this->assign('capital_is_object', is_object($this->params->{$paramName}));
		//
		// In upper-case.
		$paramName = strtoupper($paramName);
		$this->assign('upper_is_object', is_object($this->params->{$paramName}));

		return $this->status();
	}
	protected function init() {
		parent::init();

		$this->_requiredParams['GET'][] = 'param_name';
	}
}
