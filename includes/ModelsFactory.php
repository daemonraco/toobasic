<?php

namespace TooBasic;

class ModelsFactory extends Singleton {
	//
	// Protected properties.
	protected $_loadedClasses = array();
	protected $_singletons = array();
	//
	// Magic methods.
	public function __get($className) {
		return $this->get($className, false);
	}
	public function get($className, $namespace = false) {
		$out = null;

		$namespace = $namespace ? "\\{$namespace}\\" : "\\";
		while(strpos($namespace, "\\\\") !== false) {
			$namespace = str_replace("\\\\", "\\", $namespace);
		}

		$classFileName = \TooBasic\classname($className);
		$className = "{$namespace}{$classFileName}Model";

		if(isset($this->_singletons[$className])) {
			$out = $this->_singletons[$className];
		} elseif(in_array($className, $this->_loadedClasses)) {
			$out = new $className();
		} else {
			$out = $this->loadAndGet($classFileName, $className);
		}

		return $out;
	}
	//
	// Protected methods.
	protected function loadAndGet($classFileName, $className) {
		$out = null;
		//
		// @todo There's something wrong here for multiple loadings.
		$filename = Paths::Instance()->modelPath($classFileName);
		if($filename) {
			require_once $filename;

			if(class_exists($className)) {
				$this->_loadedClasses[] = $className;

				$out = new $className();

				if($out->isSingleton()) {
					$this->_singletons[$className] = $out;
				}
			} else {
				trigger_error("Class '{$className}' is not defined.", E_USER_ERROR);
			}
		} else {
			trigger_error("Cannot load model file '{$classFileName}'.", E_USER_ERROR);
		}

		return $out;
	}
}
