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
		$out = null;

		$classFileName = ucfirst($className);
		$className = ucfirst($className)."Model";

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

		$filename = Paths::Instance()->modelPath($classFileName);
		if($filename) {
			require_once $filename;

			if(class_exists($className)) {
				$this->_loadedClasses[] = $className;

				$out = new $className();

				if($className::IsSingleton()) {
					$this->_singletons[$className] = $out;
				}
			}
		}

		return $out;
	}
}
