<?php

/**
 * @file ControllerExports.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ControllerExports
 *
 * Because giving access to a controller's methods inside a view is a security
 * issue, this proxy class export only the required methods.
 */
class ControllerExports extends AbstractExports {
	//
	// Public methods.
	/**
	 * It takes an action name an returns its rendered result.
	 * 
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	public function insert($actionName) {
		return $this->_controller->insert($actionName);
	}
	/**
	 * It takes an snippet name an returns its rendered result.
	 * 
	 * @param string $snippetName Name of the snippet to render.
	 * @param string $snippetDataSet List of assignments' name to use when
	 * rendering.
	 * @return string Result of rendering the snippet.
	 */
	public function snippet($snippetName, $snippetDataSet = false) {
		return $this->_controller->snippet($snippetName, $snippetDataSet);
	}
}
