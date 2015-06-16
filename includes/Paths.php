<?php

namespace TooBasic;

class Paths extends Singleton {
	//
	// Constants.
	const ExtensionCSS = 'css';
	const ExtensionHTML = 'html';
	const ExtensionJS = 'js';
	const ExtensionJSON = 'json';
	const ExtensionPHP = 'php';
	const ExtensionSQL = 'sql';
	const ExtensionTemplate = 'html';
	//
	// Protected properties.
	protected $_configPaths = false;
	protected $_controllerPaths = false;
	protected $_cssPaths = false;
	protected $_dbSpecCallbackPaths = false;
	protected $_dbSpecPaths = false;
	protected $_imagesPaths = false;
	protected $_jsPaths = false;
	protected $_langPaths = array();
	protected $_manifests = false;
	protected $_modelsPaths = false;
	protected $_modules = false;
	protected $_representationPaths = false;
	protected $_routesPaths = false;
	protected $_servicePaths = false;
	protected $_shellCronsPaths = false;
	protected $_shellSysPaths = false;
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
		return $this->find($this->_configPaths, false, $Paths[GC_PATHS_CONFIGS], $configName, $extension, $full);
	}
	public function controllerPath($actionName, $full = false) {
		global $Paths;
		return $this->find($this->_controllerPaths, false, $Paths[GC_PATHS_CONTROLLERS], $actionName, self::ExtensionPHP, $full);
	}
	public function cssPath($cssName, $full = false) {
		global $Paths;
		return $this->find($this->_cssPaths, true, $Paths[GC_PATHS_CSS], $cssName, self::ExtensionCSS, $full);
	}
	public function dbSpecCallbackPaths($callbackName) {
		global $Paths;
		return $this->find($this->_dbSpecCallbackPaths, false, $Paths[GC_PATHS_DBSPECSCALLBACK], $callbackName, self::ExtensionSQL, false);
	}
	public function dbSpecPaths() {
		global $Paths;
		return $this->find($this->_dbSpecPaths, false, $Paths[GC_PATHS_DBSPECS], '*', self::ExtensionJSON, true);
	}
	/**
	 * Search recursively for files matching given patterns.
	 * 
	 * @param string $dirpath Directory path where to start.
	 * @param string[] $pattern Pattern or list of patterns to look for (something like 'glob()').
	 * @param boolean $hiddens Whether to include or not hidden files.
	 * @param boolean $includeDirpaths Whether to include or not directory paths.
	 * @return string[] Returns a list of found files.
	 */
	public function findAll($dirpath, $pattern = '*', $hiddens = false, $includeDirpaths = false) {
		$out = array();

		$realpath = realpath($dirpath);
		$patterns = is_array($pattern) ? $pattern : array($pattern);
		if($realpath && is_readable($realpath) && is_dir($realpath)) {
			$d = dir($realpath);
			while(false !== ($entry = $d->read())) {
				$fullPath = $realpath.DIRECTORY_SEPARATOR.$entry;
				if(preg_match('/^([\.]{1,2})$/', $entry)) {
					continue;
				}

				if(!$hiddens && preg_match('/^\./', $entry)) {
					continue;
				}

				if(is_dir($fullPath)) {
					if($includeDirpaths) {
						$out[] = $fullPath;
					}
					$out = array_merge($out, $this->findAll($fullPath, $patterns));
				} else {
					foreach($patterns as $pat) {
						if(fnmatch($pat, $entry)) {
							$out[] = $fullPath;
							break;
						}
					}
				}
			}
			$d->close();
		}

		return array_unique($out);
	}
	public function imagePath($imageName, $imageExtension, $full = false) {
		global $Paths;
		return $this->find($this->_imagesPaths, true, $Paths[GC_PATHS_IMAGES], $imageName, $imageExtension, $full);
	}
	public function jsPath($jsName, $full = false) {
		global $Paths;
		return $this->find($this->_jsPaths, true, $Paths[GC_PATHS_JS], $jsName, self::ExtensionJS, $full);
	}
	public function langNonBuiltPaths() {
		global $Paths;
		return $this->genPaths($Paths[GC_PATHS_LANGS]);
	}
	public function langPaths($lang) {
		global $Paths;

		if(!isset($this->_langPaths[$lang])) {
			global $Defaults;
			global $Directories;

			if($Defaults[GC_DEFAULTS_LANGS_BUILT]) {
				$this->_langPaths[$lang] = array();
				$this->_langPaths[$lang][] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$Paths[GC_PATHS_LANGS]}");
			} else {
				$this->_langPaths[$lang] = $this->genPaths($Paths[GC_PATHS_LANGS]);
			}
		}

		$out = $this->find($this->_langPaths[$lang], false, $Paths[GC_PATHS_LANGS], $lang, self::ExtensionJSON, true);
		foreach($out as $pos => $path) {
			if(!is_readable($path)) {
				unset($out[$pos]);
			}
		}

		return $out;
	}
	public function modelPath($modelName, $full = false) {
		global $Paths;
		return $this->find($this->_modelsPaths, false, $Paths[GC_PATHS_MODELS], $modelName, self::ExtensionPHP, $full);
	}
	public function manifests() {
		return $this->_manifests;
	}
	public function modules() {
		return $this->_modules;
	}
	public function representationPath($representationName, $full = false) {
		global $Paths;
		return $this->find($this->_representationPaths, false, $Paths[GC_PATHS_REPRESENTATIONS], $representationName, self::ExtensionPHP, $full);
	}
	public function routesPaths() {
		global $Paths;
		return $this->find($this->_routesPaths, false, $Paths[GC_PATHS_CONFIGS], 'routes', self::ExtensionJSON, true);
	}
	public function servicePath($serviceName, $full = false) {
		global $Paths;
		return $this->find($this->_servicePaths, false, $Paths[GC_PATHS_SERVICES], $serviceName, self::ExtensionPHP, $full);
	}
	public function shellCron($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellCronsPaths, false, $Paths[GC_PATHS_SHELL_CRONS], $name, self::ExtensionPHP, $full);
	}
	public function shellSys($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellSysPaths, false, $Paths[GC_PATHS_SHELL_SYSTOOLS], $name, self::ExtensionPHP, $full);
	}
	public function shellTool($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellToolsPaths, false, $Paths[GC_PATHS_SHELL_TOOLS], $name, self::ExtensionPHP, $full);
	}
	public function snippetPath($snippetName, $full = false) {
		global $Paths;
		return $this->find($this->_snippetsPaths, true, $Paths[GC_PATHS_SNIPPETS], $snippetName, self::ExtensionTemplate, $full);
	}
	public function templateDirs() {
		if($this->_templatesPaths === false) {
			global $Paths;
			$this->_templatesPaths = $this->genPaths($Paths[GC_PATHS_TEMPLATES], true);
		}

		return $this->_templatesPaths;
	}
	public function templatePath($templateName, $mode = false) {
		//
		// Global dependencies.
		global $Paths;
		//
		// Loading available templates paths.
		$this->templateDirs();
		//
		// Default mode.
		if(!$mode) {
			$mode = 'action';
		}
		//
		// Looking for and returning template's path.
		return $this->find($this->_templatesPaths, true, $Paths[GC_PATHS_TEMPLATES], Sanitizer::DirPath("{$mode}/{$templateName}"), self::ExtensionTemplate, false);
	}
	//
	// Protected methods.
	protected function genPaths($folders, $skin = false) {
		$list = array();

		global $Directories;
		//
		// If the skin is just a true boolean value, it's asking for the
		// default skin.
		if($skin === true) {
			global $SkinName;
			$skin = $SkinName;
		}
		//
		// Building the list of skin subpaths to use @{
		$skinDirs = array();
		if($skin) {
			global $Paths;
			$skinDirs[] = "{$Paths[GC_PATHS_SKINS]}/{$skin}";
		}
		//
		// Adding the no-skin subpath.
		$skinDirs[] = '';
		// @}
		//
		// At this point '$folders' must be a list, if not it is
		// transformed.
		if(!is_array($folders)) {
			$folders = array($folders);
		}
		//
		// Loading all modules.
		$this->loadModules();
		//
		// Adding folders inside every skin subpaths.
		foreach($skinDirs as $skinDir) {
			//
			// Adding folders inside every module subpaths.
			foreach($this->_modules as $module) {
				//
				// Adding folders inside every specified subpaths.
				foreach($folders as $subpath) {
					$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$module}/{$skinDir}/{$subpath}");
				}
			}
			//
			// Adding folders inside the site's path.
			foreach($folders as $subpath) {
				$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$skinDir}/{$subpath}");
			}
		}
		//
		// Adding system directory.
		foreach($folders as $subpath) {
			$list[] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SYSTEM]}/{$subpath}");
		}
		//
		// Returning the built list.
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
	protected function find(&$list, $skin, $folders, $name, $extension, $full = false, $asUri = false) {
		$out = array();

		$debugPathsActive = isset(Params::Instance()->debugpaths);
		$debugPaths = array();

		if($list === false) {
			$list = $this->genPaths($folders, $skin);
		}

		$filename = $name;
		if($extension) {
			$filename = "{$filename}.{$extension}";
		}

		foreach($list as $path) {
			$filepath = Sanitizer::DirPath("{$path}/{$filename}");
			foreach(glob($filepath) as $subpath) {
				if(is_readable($subpath)) {
					$out[] = $subpath;
				}
			}
		}

		if($debugPathsActive) {
			$debugPaths['name'] = $name;
			$debugPaths['extension'] = $extension;
			$debugPaths['skin'] = $skin;
			$debugPaths['subfolders'] = $folders;
			$debugPaths['possibilities'] = $list;
		}

		if(!$full) {
			$out = count($out) > 0 ? array_shift($out) : false;
		}

		if($debugPathsActive) {
			$debugPaths['result'] = $out;

			\TooBasic\debugThing($debugPaths);
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
				if(!preg_match('/([\._])(.*)/', $path['filename'])) {
					$this->_modules[] = $path['filename'];

					$this->_manifests[$path['filename']] = false;
					if(is_readable("{$pathname}/manifest.json")) {
						$this->_manifests[$path['filename']] = "{$pathname}/manifest.json";
					}
				}
			}
		}
	}
	//
	// Public class methods.
	public static function Path2Uri($path) {
		return Sanitizer::UriPath(substr($path, strlen(Params::Instance()->server->DOCUMENT_ROOT)));
	}
}
