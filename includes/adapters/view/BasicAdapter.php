<?php

/**
 * @file BasicAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

/**
 * @class BasicAdapter
 * @abstract
 * This absctrat adapter represent the basic functionality of a view adapter that
 * generates a simple output.
 */
abstract class BasicAdapter extends Adapter {
	//
	// Protected properties.
	/**
	 * @var string[] List of assignments that have to ve clean before
	 * rendering to avoid encoding problems.
	 */
	protected $_noise = [
		'tr',
		'ctrl'
	];
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// By default, all basic adapters return some kind of text, but it
		// can be changed on inherited classes.
		$this->_headers['Content-Type'] = 'text/plain';
	}
	//
	// Public methods.
	/**
	 * Allows access to a list of assignments that may cause problems when
	 * rendering.
	 *
	 * @return string[] Retunrs a list of values.
	 */
	public function noise() {
		return $this->_noise;
	}
	//
	// Protected methods.
	/**
	 * This method replaces any assignment considered to be noise with some
	 * basic text.
	 *
	 * @param mixed[string] $assignments List of assignments to be analysed.
	 * @return mixed[string] Returns a cleaner version of the assignments
	 * given as parameter.
	 */
	protected function cleanRendering($assignments) {
		//
		// Including this class' auto assignments.
		$merge = array_merge($this->_autoAssigns, $assignments);
		//
		// Checking each value considered to be noise.
		foreach($this->noise() as $key) {
			//
			// Repacing undesired assingments with a basic text.
			if(isset($merge[$key])) {
				$merge[$key] = '--REMOVED--';
			}
		}
		//
		// Returning a cleaner version of the assignments.
		return $merge;
	}
}
