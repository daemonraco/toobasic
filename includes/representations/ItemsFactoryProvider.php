<?php

/**
 * @file ItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

class ItemsFactoryProvider extends Singleton {
	//
	// Protected properties.
	protected $_loadedClases = array();
	//
	// Magic methods.
	public function __get($name) {
		$fullName = $this->loadClass($name);
		return $fullName ? $fullName ::Instance() : null;
	}
	public function __call($name, $args) {
		$fullName = $this->loadClass($name);
		return $fullName ? $fullName ::Instance(isset($args[0]) ? $args[0] : false) : null;
	}
	//
	// Protected methods.
	protected function loadClass($name) {
		$out = false;

		if(!isset($this->_loadedClases[$name])) {
			$fullName = classname($name)."Factory";

			$filename = Paths::Instance()->representationPath($fullName);
			if($filename) {
				require_once $filename;

				if(class_exists($fullName)) {
					$this->_loadedClases[$name] = $fullName;
					$out = $fullName;
				} else {
					trigger_error("Class '{$fullName}' is not defined.", E_USER_ERROR);
				}
			} else {
				trigger_error("Cannot load items representation factory '{$fullName}'.", E_USER_ERROR);
			}
		} else {
			$out = $this->_loadedClases[$name];
		}

		return $out;
	}
}
