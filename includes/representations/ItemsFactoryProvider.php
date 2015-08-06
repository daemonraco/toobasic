<?php

/**
 * @file ItemsFactoryProvider.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

use TooBasic\Names;
use TooBasic\Paths;

/**
 * @class ItemsFactoryProvider
 */
class ItemsFactoryProvider extends \TooBasic\Singleton {
	//
	// Protected properties.
	protected $_loadedClases = array();
	//
	// Magic methods.
	public function __get($name) {
		return $this->get($name);
	}
	public function __call($name, $args) {
		//
		// Is there a namespace specified.
		if(isset($args[1])) {
			$namespace = "{$args[1]}\\";
			while(strpos($namespace, '\\\\') !== false) {
				$namespace = str_replace('\\\\', '\\', $namespace);
			}
			$name = $namespace.$name;
		}
		return $this->get($name, isset($args[0]) ? $args[0] : false);
	}
	public function get($name, $dbname = false) {
		$fullName = $this->loadClass($name);
		return $fullName ? $fullName ::Instance($dbname) : null;
	}
	//
	// Protected methods.
	protected function loadClass($name) {
		$out = false;

		$className = Names::ItemsFactoryClass($name);

		if(!isset($this->_loadedClases[$className])) {
			$filename = Paths::Instance()->representationPath(Names::ItemsFactoryFilename($name));
			if($filename) {
				require_once $filename;

				if(class_exists($className)) {
					$this->_loadedClases[$name] = $className;
					$out = $className;
				} else {
					throw new Exception("Class '{$className}' is not defined.");
				}
			} else {
				throw new Exception("Cannot load items representation factory '{$className}'.");
			}
		} else {
			$out = $this->_loadedClases[$className];
		}

		return $out;
	}
}
