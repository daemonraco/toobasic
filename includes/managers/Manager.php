<?php

/**
 * @file Manager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Manager
 * @abstract
 */
abstract class Manager extends Singleton {
	//
	// Magic methods.
	/**
	 * @todo doc
	 *
	 * @param string $prop @todo doc
	 * @return mixed @todo doc
	 */
	public function __get($prop) {
		$out = false;

		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			
		}

		return $out;
	}
}
