<?php

namespace TooBasic;

abstract class ViewAdapter extends Adapter {
	//
	// Protected properties.
	/**
	 * @var mixed[string]
	 */
	protected $_autoAssigns = array();
	/**
	 * @var string[string]
	 */
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
		$this->_autoAssigns["ROOTDIR"] = ROOTDIR;
		$this->_autoAssigns["ROOTURL"] = ROOTURI;
		$this->_autoAssigns["SERVER"] = $_SERVER;

//		global $Defaults;
//		$this->_autoAssigns["defaults"] = $Defaults;/** @todo SECURITY ISSUES */
	}
	public function headers() {
		return $this->_headers;
	}
	abstract public function render($assignments, $action = false);
}
