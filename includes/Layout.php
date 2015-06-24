<?php

/**
 * @file Layout.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @abstract
 * @class Layout
 * This is a sub-type of controllers used for layouts.
 */
abstract class Layout extends Controller {
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
		//
		// Adding basic parametres for cache keys.
		$this->_cacheParams["GET"][] = "action";
		$this->_cacheParams["GET"][] = "mode";
	}
}
