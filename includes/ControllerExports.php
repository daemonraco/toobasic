<?php

namespace TooBasic;

class ControllerExports {
	//
	// Protected properties.
	protected $_controller = false;
	//
	// Magic methods.
	public function __construct($ctrl) {
		$this->_controller = $ctrl;
	}
	//
	// Public methods.
	public function css($styleName) {
		return Paths::Path2Uri(Paths::Instance()->cssPath($styleName));
	}
	/**
	 * It takes an action name an returns its rendered result.
	 * 
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	public function img($imageName,$imageExtension) {
		return Paths::Path2Uri(Paths::Instance()->imagePath($imageName,$imageExtension));
	}
	public function insert($actionName) {
		return $this->_controller->insert($actionName);
	}
	public function js($scriptName) {
		return Paths::Path2Uri(Paths::Instance()->jsPath($scriptName));
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
