<?php

/**
 * @file XML.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

//
// Class aliases.
use \TooBasic\Exception;

/**
 * @class XML
 * This view adapter provides a way to show current assignments in XML format.
 */
class XML extends BasicAdapter {
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Setting a proper content type.
		$this->_headers["Content-Type"] = 'application/xml';
	}
	//
	// Public methods.
	/**
	 * This method is the one in charge of rendering the output using a list
	 * of assignments and retruning it for further process.
	 *
	 * @param mixed[string] $assignments List of assignments to be analysed.
	 * @param string $template Provided for compatibility.
	 * @return string Retruns a view rendering result.
	 */
	public function render($assignments, $template) {
		//
		// Controling the existence of 'xmlrpc_encode()' because it's
		// experimental on PHP 5.
		if(!function_exists('xmlrpc_encode')) {
			throw new Exception("Funciton 'xmlrpc_encode()' is not defined");
		}
		//
		// Rendering a clean list of assignments and encoding it as XML.
		return \xmlrpc_encode($this->cleanRendering($assignments));
	}
}
