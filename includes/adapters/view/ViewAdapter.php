<?php

/**
 * @file ViewAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class ViewAdapter
 * This class represents the basic structure for a view adapter.
 */
abstract class ViewAdapter extends Adapter {
	//
	// Protected properties.
	/**
	 * @var mixed[string] Full list of values assigned by default.
	 */
	protected $_autoAssigns = array();
	/**
	 * @var string[string] List of headers to be exported on the HTTP
	 * response.
	 */
	protected $_headers = array();
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->_templateDirs = Paths::Instance()->templateDirs();
	}
	//
	// Public methods.
	/**
	 * This method adds some generic assignment like:
	 * 	- current site directory.
	 * 	- current site URI.
	 * 	- current server variables.
	 */
	public function autoAssigns() {
		$this->_autoAssigns["ROOTDIR"] = ROOTDIR;
		$this->_autoAssigns["ROOTURL"] = ROOTURI;
		$this->_autoAssigns["SERVER"] = Params::Instance()->allOf(Params::TypeSERVER);

//		global $Defaults;
//		$this->_autoAssigns["defaults"] = $Defaults;/** @todo SECURITY ISSUES */
	}
	/**
	 * Allows all headers to be set on a HTTP response.
	 * 
	 * @return string[string] List of headers and their values.
	 */
	public function headers() {
		return $this->_headers;
	}
	/**
	 * @abstract
	 * 
	 * @param string[string] $assignments List of assignment to use when
	 * rendering.
	 * @param string $template Name of the template to render.
	 * @return string Returns the rendered result.
	 */
	abstract public function render($assignments, $template);
}
