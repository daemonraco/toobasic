<?php

abstract class ViewAdapter extends Adapter {
	//
	// Constants.
	//
	// Protected properties.
	protected $_autoAssigns = array();
	protected $_headers = array();
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->_templateDirs = Paths::Instance()->templateDirs();
	}
	//
	// Public methods.
	public function autoAssigns() {
		global $Defaults;

		$this->_autoAssigns["ROOTDIR"] = ROOTDIR;
		$this->_autoAssigns["ROOTURL"] = ROOTURI;
		$this->_autoAssigns["SERVER"] = $_SERVER;
		$this->_autoAssigns["defaults"] = $Defaults;
	}
	public function headers() {
		return $this->_headers;
	}
	abstract public function render($assignments, $action = false);
	//
	// Protected methods.
}
