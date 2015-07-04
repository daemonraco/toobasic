<?php

/**
 * @file EmailExports.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class EmailExports
 *
 * Because giving access to a email controller's methods inside a view is a
 * security issue, this proxy class export only the required methods.
 */
class EmailExports extends AbstractExports {
	//
	// Protected properties.
	protected $_payload = false;
	/**
	 * Class constructor.
	 * 
	 * @param \TooBasic\Email $ctrl Controller to represent.
	 */
	public function __construct(\TooBasic\Email $ctrl) {
		parent::__construct($ctrl);
		$this->_payload = $ctrl->payload();
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
		$out = parent::css($styleName);
		return $out ? $this->_payload->server().$out : '';
	}
	/**
	 * Exports a way to get an image URI.
	 * 
	 * @param string $imageName Name of the image to look for.
	 * @param string $imageExtension Image's extension.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	public function img($imageName, $imageExtension = 'png') {
		$out = parent::img($imageName, $imageExtension);
		return $out ? $this->_payload->server().$out : '';
	}
	/**
	 * Exports a way to get a javascript file URI.
	 * 
	 * @param string $scriptName Name of the script to look for.
	 * @return string If found it returns an absolute URI, otherwise it
	 * returns false.
	 */
	public function js($scriptName) {
		$out = parent::js($scriptName);
		return $out ? $this->_payload->server().$out : '';
	}
	/**
	 * It takes a relative path inside ROOTDIR/libraries and returns it as a
	 * full uri path.
	 * 
	 * @param string $libPath Library elemet to be rendered.
	 * @return string Rendered result.
	 */
	public function lib($libPath) {
		$out = parent::lib($libPath);
		return $out ? $this->_payload->server().$out : '';
	}
	/**
	 * Takes a link url from, for example, an anchor and change it into
	 * something cleaner, adding an absolute prefix and, if possible,
	 * converting it into a format for routes analysis.
	 * 
	 * @param string $link Link to check and transform.
	 * @return string Returns a well formated url.
	 */
	public function link($link = '') {
		$out = parent::link($link);
		return $out ? $this->_payload->server().$out : '';
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
