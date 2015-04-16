<?php

namespace TooBasic;

/**
 * @class ControllerExports
 *
 * Because giving access to a controller's methods inside a view is a security
 * issue, this proxy class export only the required methods.
 */
class ControllerExports {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Controller Exported controller pointer.
	 */
	protected $_controller = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 * 
	 * @param \TooBasic\Controller $ctrl Controller to represent.
	 */
	public function __construct($ctrl) {
		$this->_controller = $ctrl;
	}
	//
	// Public methods.
	/**
	 * Exports a way to get a stylesheet URI.
	 * 
	 * @param string $styleName Name of the stylesheet to look for.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	public function css($styleName) {
		return Paths::Path2Uri(Paths::Instance()->cssPath($styleName));
	}
	/**
	 * Exports a way to get an image URI.
	 * 
	 * @param string $imageName Name of the image to look for.
	 * @param string $imageExtension Image's extension.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	public function img($imageName, $imageExtension = "png") {
		return Paths::Path2Uri(Paths::Instance()->imagePath($imageName, $imageExtension));
	}
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
	 * Exports a way to get a javascript file URI.
	 * 
	 * @param string $scriptName Name of the script to look for.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
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
