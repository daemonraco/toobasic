<?php

class Paths extends Singleton {
	//
	// Protected properties.
	protected $_controllerPaths = false;
	protected $_manifests = false;
	protected $_modelsPaths = false;
	protected $_modules = false;
	//
	// Public methods.
	/**
	 * 
	 * @param type $actionName
	 * @return string
	 */
	public function configPaths() {
		global $Directories;

		$out = array();

		foreach($this->_modules as $module) {
			$out[] = "{$Directories["modules"]}/{$module}/config.php";
		}
		sort($out);

		$out = array_merge(array(ROOTDIR."/config.php"), $out);

		foreach($out as $key => $path) {
			if(!is_readable($path)) {
				unset($out[$key]);
			}
		}

		return $out;
	}
	public function controllerPath($actionName) {
		$out = false;

		global $Directories;
		global $Paths;

		if($this->_controllerPaths === false) {
			$this->loadModules();
			$this->_controllerPaths = array();

			foreach($this->_modules as $module) {
				$this->_controllerPaths[] = Sanitizer::DirPath("{$Directories["modules"]}/{$module}/{$Paths["controllers"]}\n");
			}
			$this->_controllerPaths[] = Sanitizer::DirPath("{$Directories["site"]}/{$Paths["controllers"]}");

			if(isset($_REQUEST["_debugcontrollerspath"])) {
				debugit($this->_controllerPaths, true);
			}
		}

		$filename = "{$actionName}.php";
		foreach($this->_controllerPaths as $path) {
			$filepath = Sanitizer::DirPath("{$path}/{$filename}");
			if(is_readable($filepath)) {
				$out = $filepath;
				break;
			}
		}

		return $out;
	}
	public function modelPath($modelName) {
		$out = false;

		global $Directories;
		global $Paths;

		if($this->_modelsPaths === false) {
			$this->loadModules();
			$this->_modelsPaths = array();

			foreach($this->_modules as $module) {
				$this->_modelsPaths[] = Sanitizer::DirPath("{$Directories["modules"]}/{$module}/{$Paths["models"]}\n");
			}
			$this->_modelsPaths[] = Sanitizer::DirPath("{$Directories["site"]}/{$Paths["models"]}");

			if(isset($_REQUEST["_debugmodelspath"])) {
				debugit($this->_modelsPaths, true);
			}
		}

		$filename = "{$modelName}.php";
		foreach($this->_modelsPaths as $path) {
			$filepath = Sanitizer::DirPath("{$path}/{$filename}");
			if(is_readable($filepath)) {
				$out = $filepath;
				break;
			}
		}

		return $out;
	}
	public function manifests() {
		return $this->_manifests;
	}
	public function modules() {
		return $this->_modules;
	}
	//
	// Protected methods.
	protected function loadModules() {
		if($this->_modules === false) {
			global $Directories;


			$this->_modules = array();
			$this->_manifests = array();
			foreach(glob("{$Directories["modules"]}/*", GLOB_ONLYDIR) as $pathname) {
				$path = pathinfo($pathname);
				if(!preg_match('/([\._])(.*)/', $path["filename"])) {
					$this->_modules[] = $path["filename"];

					$this->_manifests[$path["filename"]] = false;
					if(is_readable("{$pathname}/manifest.json")) {
						$this->_manifests[$path["filename"]] = "{$pathname}/manifest.json";
					}
				}
			}
		}
	}
}
