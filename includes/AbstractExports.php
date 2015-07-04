<?php

/**
 * @file AbstractExports.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class AbstractExports
 *
 * Because giving access to a controller's methods inside a view is a security
 * issue, this proxy class export only the required methods.
 */
abstract class AbstractExports {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\AbstractExporter Exported controller pointer.
	 */
	protected $_controller = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 * 
	 * @param \TooBasic\AbstractExporter $ctrl Controller to represent.
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
	abstract public function css($styleName);
	/**
	 * Exports a way to get an image URI.
	 * 
	 * @param string $imageName Name of the image to look for.
	 * @param string $imageExtension Image's extension.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	abstract public function img($imageName, $imageExtension = "png");
	/**
	 * It takes an action name an returns its rendered result.
	 * 
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	abstract public function insert($actionName);
	/**
	 * Exports a way to get a javascript file URI.
	 * 
	 * @param string $scriptName Name of the script to look for.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	abstract public function js($scriptName);
	/**
	 * It takes a relative path inside ROOTDIR/libraries and returns it as a
	 * full uri path.
	 * 
	 * @param string $libPath Library elemet to be rendered.
	 * @return string Rendered result.
	 */
	abstract function lib($libPath);
	/**
	 * Takes a link url from, for example, an anchor and change it into
	 * something cleaner, adding an absolute prefix and, if possible,
	 * converting it into a format for routes analysis.
	 * 
	 * @param string $link Link to check and transform.
	 * @return string Returns a well formated url.
	 */
	abstract public function link($link = '');
	/**
	 * It takes an snippet name an returns its rendered result.
	 * 
	 * @param string $snippetName Name of the snippet to render.
	 * @param string $snippetDataSet List of assignments' name to use when
	 * rendering.
	 * @return string Result of rendering the snippet.
	 */
	abstract public function snippet($snippetName, $snippetDataSet = false);
}
