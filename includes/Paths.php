<?php

namespace TooBasic;

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
	protected $_langPaths = array();
	protected $_manifests = false;
	protected $_modelsPaths = false;
	protected $_modules = false;
	protected $_representationPaths = false;
	protected $_servicePaths = false;
	protected $_shellCronsPaths = false;
	protected $_shellToolsPaths = false;
	protected $_snippetsPaths = false;
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
			$this->_configPaths = $this->genPaths($Paths[GC_PATHS_CONFIGS]);
			array_unshift($this->_configPaths, $Directories[GC_DIRECTORIES_CONFIGS]);
		}

		return $this->find($this->_configPaths, $Paths[GC_PATHS_CONFIGS], $configName, $extension, $full);
	}
	public function controllerPath($actionName, $full = false) {
		global $Paths;
		return $this->find($this->_controllerPaths, $Paths[GC_PATHS_CONTROLLERS], $actionName, self::ExtensionPHP, $full);
	}
	public function cssPath($cssName, $full = false) {
		global $Paths;
		return $this->find($this->_cssPaths, $Paths[GC_PATHS_CSS], $cssName, self::ExtensionCSS, $full);
	}
	public function jsPath($jsName, $full = false) {
		global $Paths;
		return $this->find($this->_jsPaths, $Paths[GC_PATHS_JS], $jsName, self::ExtensionJS, $full);
	}
	public function langPaths($lang) {
		global $Paths;

		if(!isset($this->_langPaths[$lang])) {
			global $Defaults;
			global $Directories;

			if($Defaults["langs-built"]) {
				$this->_langPaths[$lang] = array();
				$this->_langPaths[$lang][] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$Paths[GC_PATHS_LANGS]}");
			} else {
				$this->_langPaths[$lang] = $this->genPaths($Paths[GC_PATHS_LANGS]);
			}
		}

		$out = $this->find($this->_langPaths[$lang], $Paths[GC_PATHS_LANGS], $lang, self::ExtensionJSON, true);
		foreach($out as $pos => $path) {
			if(!is_readable($path)) {
				unset($out[$pos]);
			}
		}

		return $out;
	}
	public function modelPath($modelName, $full = false) {
		global $Paths;
		return $this->find($this->_modelsPaths, $Paths[GC_PATHS_MODELS], $modelName, self::ExtensionPHP, $full);
	}
	public function manifests() {
		return $this->_manifests;
	}
	public function modules() {
		return $this->_modules;
	}
	public function representationPath($representationName, $full = false) {
		global $Paths;
		return $this->find($this->_representationPaths, $Paths[GC_PATHS_REPRESENTATIONS], $representationName, self::ExtensionPHP, $full);
	}
	public function servicePath($serviceName, $full = false) {
		global $Paths;
		return $this->find($this->_servicePaths, $Paths[GC_PATHS_SERVICES], $serviceName, self::ExtensionPHP, $full);
	}
	public function shellCron($name, $full = false) {
		global $Paths;

		if($this->_shellCronsPaths === false) {
			$this->_shellCronsPaths = $this->genPaths($Paths[GC_PATHS_SHELL_CRONS]);
			$this->_shellCronsPaths[] = Sanitizer::DirPath(ROOTDIR."/{$Paths[GC_PATHS_SHELL_CRONS]}");
		}

		return $this->find($this->_shellCronsPaths, $Paths[GC_PATHS_SHELL_CRONS], $name, self::ExtensionPHP, $full);
	}
	public function shellTool($name, $full = false) {
		global $Paths;

		if($this->_shellToolsPaths === false) {
			$this->_shellToolsPaths = $this->genPaths($Paths[GC_PATHS_SHELL_TOOLS]);
			$this->_shellToolsPaths[] = Sanitizer::DirPath(ROOTDIR."/{$Paths[GC_PATHS_SHELL_TOOLS]}");
		}

		return $this->find($this->_shellToolsPaths, $Paths[GC_PATHS_SHELL_TOOLS], $name, self::ExtensionPHP, $full);
	}
	public function snippetPath($snippetName, $full = false) {
		global $Paths;
		return $this->find($this->_snippetsPaths, $Paths[GC_PATHS_SNIPPETS], $snippetName, self::ExtensionTemplate, $full);
	}
	public function templateDirs() {
		global $Paths;

		if($this->_templatesPaths === false) {
			global $Directories;
			$this->_templatesPaths = $this->genPaths($Paths[GC_PATHS_TEMPLATES]);
			array_unshift($this->_templatesPaths, Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_TEMPLATES]}"));
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
			$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$module}/{$folder}");
		}
		$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$folder}");

		return $list;
	}
	protected function genSitePaths($folder) {
		$list = array();

		global $Directories;

		$this->loadModules();
		foreach($this->_modules as $module) {
			$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$module}/{$folder}");
		}
		$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$folder}");

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
			foreach(glob("{$Directories[GC_DIRECTORIES_MODULES]}/*", GLOB_ONLYDIR) as $pathname) {
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
