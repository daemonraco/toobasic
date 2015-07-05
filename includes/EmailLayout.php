<?php

/**
 * @file EmailLayout.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class Layout
 * This is a sub-type of email controllers used for email layouts.
 */
abstract class EmailLayout extends Email {
	//
	// Magic methods.
	/**
	 * Class constructor
	 *
	 * @param \TooBasic\EmailPayload $emailPayload 
	 */
	public function __construct($emailPayload) {
		parent::__construct($emailPayload);
		//
		// Picking a name for this controller's view. It could be
		// overriden by a controller allowing flex views.
		$this->_viewName = $this->_payload->layout();
	}
	//
	// Public methods.
	/**
	 * This dummy method prevent any other object to think this layout has
	 * another layout in which it is injected.
	 *
	 * @return string It always returns false.
	 */
	public function layout() {
		return false;
	}
	//
	// Protected methods.
	/**
	 * Basic initialization for any layout.
	 */
	protected function init() {
		parent::init();
	}
}
