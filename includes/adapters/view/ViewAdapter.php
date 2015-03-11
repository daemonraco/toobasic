<?php

abstract class ViewAdapter extends Adapter {
	//
	// Constants.
	//
	// Protected properties.
	protected $_useLayout = true;
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->_templateDirs = Paths::Instance()->templateDirs();
	}
	//
	// Public methods.
	public function disableLayout() {
		$this->_useLayout = false;
	}
	public function enableLayout() {
		$this->_useLayout = true;
	}
	abstract public function render($assignments, $action = false);
	//
	// Protected methods.
}
