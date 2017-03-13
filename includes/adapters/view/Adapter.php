<?php

/**
 * @file Adapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

//
// Class aliases.
use \TooBasic\Params;
use \TooBasic\Paths;

/**
 * @class Adapter
 * @abstract
 * This class represents the basic structure for a view adapter.
 */
abstract class Adapter extends \TooBasic\Adapters\Adapter {
	//
	// Protected properties.
	/**
	 * @var mixed[string] Full list of values assigned by default.
	 */
	protected $_autoAssigns = [];
	/**
	 * @var string[string] List of headers to be exported on the HTTP
	 * response.
	 */
	protected $_headers = [];
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
		$this->_autoAssigns['ROOTDIR'] = ROOTDIR;
		$this->_autoAssigns['ROOTURL'] = ROOTURI;
		$this->_autoAssigns['SERVER'] = Params::Instance()->allOf(Params::TYPE_SERVER);
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
