<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

class ItemsFactoryStack extends Singleton {
	//
	// Protected properties.
	protected $_singletons = array();
	//
	// Magic methods.
	public function __get($name) {
		$out = null;

		if(isset($this->_singletons[$name])) {
			$out = $this->_singletons[$name];
		} else {
			$name = classname($name)."Factory";

			$filename = Paths::Instance()->representationPath($name);
			if($filename) {
				require_once $filename;

				if(class_exists($name)) {
					$out = $name::Instance();
					$this->_singletons[$name] = $out;
				} else {
					trigger_error("Class '{$name}' is not defined.", E_USER_ERROR);
				}
			} else {
				trigger_error("Cannot load items representation factory '{$name}'.", E_USER_ERROR);
			}
		}

		return $out;
	}
}
