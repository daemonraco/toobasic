<?php

class Paths extends Singleton {
	//
	// Constants.
	const ExtensionCSS = "css";
	const ExtensionHTML = "html";
	const ExtensionJS = "js";
	const ExtensionJSON = "json";
	const ExtensionPHP = "php";
	const ExtensionTemplate = "html";
	//
	// Protected properties.
	protected $_configPaths = false;
	protected $_controllerPaths = false;
	protected $_cssPaths = false;
	protected $_jsPaths = false;
	protected $_manifests = false;
	protected $_modelsPaths = false;
	protected $_modules = false;
	protected $_templatesPaths = false;
	//
	// Public methods.
	/**
	 * 
	 * @param type $actionName
	 * @return string
	 */
	public function configPath($configName, $extension = self::ExtensionPHP, $full = false) {
		global $Paths;

		if($this->_configPaths === false) {
			global $Directories;
			$this->_configPaths = $this->genPaths($Paths["configs"]);
			array_unshift($this->_configPaths, $Directories["configs"]);
		}

		$out = $this->find($this->_configPaths, $Paths["configs"], $configName, $extension, $full);

		return $out;
	}
	public function controllerPath($actionName, $full = false) {
		global $Paths;
		return $this->find($this->_controllerPaths, $Paths["controllers"], $actionName, self::ExtensionPHP, $full);
	}
	public function cssPath($cssName, $full = false) {
		global $Paths;
		return $this->find($this->_cssPaths, $Paths["css"], $cssName, self::ExtensionCSS, $full);
	}
	public function jsPath($jsName, $full = false) {
		global $Paths;
		return $this->find($this->_jsPaths, $Paths["js"], $jsName, self::ExtensionJS, $full);
	}
	public function modelPath($modelName, $full = false) {
		global $Paths;
		return $this->find($this->_modelsPaths, $Paths["models"], $modelName, self::ExtensionPHP, $full);
	}
	public function manifests() {
		return $this->_manifests;
	}
	public function modules() {
		return $this->_modules;
	}
	public function templateDirs() {
		global $Paths;

		if($this->_templatesPaths === false) {
			global $Directories;
			$this->_templatesPaths = $this->genPaths($Paths["templates"]);
			array_unshift($this->_templatesPaths, Sanitizer::DirPath("{$Directories["site"]}/{$Paths["templates"]}"));
			$this->_templatesPaths = array_unique($this->_templatesPaths);
		}

		return $this->_templatesPaths;
	}
	//
	// Protected methods.
	protected function genPaths($folder) {
		$list = array();

		global $Directories;

		$this->loadModules();
		foreach($this->_modules as $module) {
			$list[] = Sanitizer::DirPath("{$Directories["modules"]}/{$module}/{$folder}");
		}
		$list[] = Sanitizer::DirPath("{$Directories["site"]}/{$folder}");

		return $list;
	}
	protected function genSitePaths($folder) {
		$list = array();

		global $Directories;

		$this->loadModules();
		foreach($this->_modules as $module) {
			$list[] = Sanitizer::DirPath("{$Directories["site"]}/{$module}/{$folder}");
		}
		$list[] = Sanitizer::DirPath("{$Directories["site"]}/{$folder}");

		return $list;
	}
	protected function find(&$list, $folder, $name, $extension, $full = false) {
		$out = array();

		if($list === false) {
			$list = $this->genPaths($folder);
		}

		$filename = $name;
		if($extension) {
			$filename = "{$filename}.{$extension}";
		}

		foreach($list as $path) {
			$filepath = Sanitizer::DirPath("{$path}/{$filename}");
			if(is_readable($filepath)) {
				$out[] = $filepath;
			}
		}

		if(!$full) {
			$out = count($out) > 0 ? array_shift($out) : false;
		}

		return $out;
	}
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
