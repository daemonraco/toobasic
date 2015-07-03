<?php

/**
 * @file Email.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

abstract class Email extends AbstractExporter {
	//
	// Protected properties.	
	/**
	 * @var string Name of the layout current controller uses. 'false' means
	 * no layout and 'null' means default layout.
	 */
	protected $_layout = null;
	/**
	 * @var string Current view template to render for this contorller.
	 */
	protected $_viewName = false;
	//
	// Magic methods.
	/**
	 * Class constructor
	 * 
	 * @param string $emailName An indentifier name for this controller, by
	 * default it's currect action's name.
	 */
	public function __construct($emailName) {
		//
		// Global requirements.
		global $Defaults;
		//
		// Picking a name for this controller's view. It could be
		// overriden by a controller allowing flex views.
		$this->_viewName = $emailName;
		//
		// It doesn't matter what it's set for the current class, there
		// are rules first.
		if(isset($this->params->debugnoemaillayout)) {
			//
			// Removing layout setting.
			$this->_layout = false;
		} elseif(isset($this->params->get->elayout)) {
			//
			// Using forced layout.
			$this->_layout = $this->params->get->elayout;
		} elseif($this->_layout === null) {
			//
			// If nothing is set, then the default is used.
			//
			// @note: NULL means there's no setting for this
			// controllers layout. FALSE means this controller uses
			// no layout.
			$this->_layout = $Defaults[GC_DEFAULTS_EMAIL_LAYOUT];
		}
	}
	//
	// Public methods.
	/**
	 * Allows to know which layout is to be used when this controller is
	 * render.
	 * 
	 * @return string Layout name. 'false' when no layout and null when it
	 * must be default.
	 */
	public function layout() {
		return $this->_layout;
	}
	public function run() {
		debugit('@todo', 1);
	}
	/**
	 * Allows to set view name for this controller.
	 * 
	 * @param string $viewName Name to be set.
	 */
	public function setViewName($viewName) {
		$this->_viewName = $viewName;
	}
	/**
	 * Allows to access the view name of this controller.
	 * 
	 * @return string Name of current view.
	 */
	public function viewName() {
		return $this->_viewName;
	}
	//
	// Protected methods.
	public function dryRun() {
		debugit('@todo', 1);
	}
}
