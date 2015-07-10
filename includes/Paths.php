<?php

/**
 * @file Paths.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Paths
 * This singleton provides a centralized administration of all known paths. For
 * example, if you need to know here certain controller file is stored, this class
 * knows the mechanism to find it.
 */
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
	/**
	 * @var string[] List of available configuration directories.
	 */
	protected $_configPaths = false;
	/**
	 * @var string[] List of available controllers directories.
	 */
	protected $_controllerPaths = false;
	/**
	 * @var string[] List of available styles directories.
	 */
	protected $_cssPaths = false;
	/**
	 * @var string[] List of available directories for database structure
	 * specifications callbacks.
	 */
	protected $_dbSpecCallbackPaths = false;
	/**
	 * @var string[] List of available directories for database structure
	 * specifications.
	 */
	protected $_dbSpecPaths = false;
	/**
	 * @var string[] List of available images directories.
	 */
	protected $_imagesPaths = false;
	/**
	 * @var string[] List of available scripts directories.
	 */
	protected $_jsPaths = false;
	/**
	 * @var string[] List of available language configurations directories.
	 */
	protected $_langPaths = array();
	/**
	 * @var string[string] List of available manifests directories.
	 */
	protected $_manifests = false;
	/**
	 * @var string[] List of available models directories.
	 */
	protected $_modelsPaths = false;
	/**
	 * @var string[] List of available modules/plugins.
	 */
	protected $_modules = false;
	/**
	 * @var string[] List of available directories for table representations.
	 */
	protected $_representationPaths = false;
	/**
	 * @var string[] List of available route configurations directories.
	 */
	protected $_routesPaths = false;
	/**
	 * @var string[] List of available services directories.
	 */
	protected $_servicePaths = false;
	/**
	 * @var string[] List of available cron-tools directories.
	 */
	protected $_shellCronsPaths = false;
	/**
	 * @var string[] List of available sys-tools directories.
	 */
	protected $_shellSysPaths = false;
	/**
	 * @var string[] List of available shell-tools directories.
	 */
	protected $_shellToolsPaths = false;
	/**
	 * @var string[] List of available snippets directories.
	 */
	protected $_snippetsPaths = false;
	/**
	 * @var string[] List of available templates directories.
	 */
	protected $_templatesPaths = false;
	//
	// Public methods.
	/**
	 * This method provides a way to get one or all configuration files
	 * matching certain name and extension.
	 *
	 * @param string $configName Name of the file to look for (without
	 * extension).
	 * @param string $extension File extension, by default 'php'.
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function configPath($configName, $extension = self::ExtensionPHP, $full = false) {
		global $Paths;
		return $this->find($this->_configPaths, false, $Paths[GC_PATHS_CONFIGS], $configName, $extension, $full);
	}
	/**
	 * This method provides a way to get one or all controller files matching
	 * certain name.
	 *
	 * @param string $actionName  Name of the file to look for (extension is
	 * assumed 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function controllerPath($actionName, $full = false) {
		global $Paths;
		return $this->find($this->_controllerPaths, false, $Paths[GC_PATHS_CONTROLLERS], $actionName, self::ExtensionPHP, $full);
	}
	/**
	 * This method provides a way to get one or all style files matching
	 * certain name.
	 *
	 * @param string $cssName Name of the file to look for (extension is
	 * assumed 'css').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function cssPath($cssName, $full = false) {
		global $Paths;
		return $this->find($this->_cssPaths, true, $Paths[GC_PATHS_CSS], $cssName, self::ExtensionCSS, $full);
	}
	/**
	 * This method looks for a database structure callback file.
	 *
	 * @param string $callbackName Name of the file to look for (extension is
	 * assumed 'sql').
	 * @return string Returns an absolute path.
	 */
	public function dbSpecCallbackPaths($callbackName) {
		global $Paths;
		return $this->find($this->_dbSpecCallbackPaths, false, $Paths[GC_PATHS_DBSPECSCALLBACK], $callbackName, self::ExtensionSQL, false);
	}
	/**
	 * This method returns the full list of database structure specifications.
	 *
	 * @return string Returns a list of absolute paths.
	 */
	public function dbSpecPaths() {
		global $Paths;
		return $this->find($this->_dbSpecPaths, false, $Paths[GC_PATHS_DBSPECS], '*', self::ExtensionJSON, true);
	}
	/**
	 * Search recursively for files matching a given patterns.
	 * 
	 * @param string $dirpath Directory path where to start.
	 * @param string[] $pattern Pattern or list of patterns to look for (something like 'glob()').
	 * @param boolean $hiddens Whether to include or not hidden files.
	 * @param boolean $includeDirpaths Whether to include or not directory paths.
	 * @return string[] Returns a list of found files.
	 */
	public function findAll($dirpath, $pattern = '*', $hiddens = false, $includeDirpaths = false) {
		//
		// Default values.
		$out = array();
		//
		// Cleaning directory path.
		$realpath = realpath($dirpath);
		//
		// Enforcing '$patterns' to be a list of patterns.
		$patterns = is_array($pattern) ? $pattern : array($pattern);
		//
		// Checking permission on the directory to analyze.
		if($realpath && is_readable($realpath) && is_dir($realpath)) {
			//
			// Reading directory entries and waling over each one.
			$d = dir($realpath);
			while(false !== ($entry = $d->read())) {
				//
				// Ignoring pseudo-directories.
				if(preg_match('/^([\.]{1,2})$/', $entry)) {
					continue;
				}
				//
				// Ignoring hidden files unless they were
				// requested.
				if(!$hiddens && preg_match('/^\./', $entry)) {
					continue;
				}
				//
				// Building entry's full path.
				$fullPath = $realpath.DIRECTORY_SEPARATOR.$entry;
				//
				// Checking current entry type, directories have
				// to go recursive and files have to match a
				// pattern.
				if(is_dir($fullPath)) {
					//
					// Checking if current directory's path
					// has to be included as result.
					if($includeDirpaths) {
						$out[] = $fullPath;
					}
					//
					// Going one level down.
					$out = array_merge($out, $this->findAll($fullPath, $patterns));
				} else {
					//
					// Checking each pattern and adding the
					// path when it matches at least one.
					foreach($patterns as $pat) {
						if(fnmatch($pat, $entry)) {
							$out[] = $fullPath;
							break;
						}
					}
				}
			}
			//
			// Closing directory descriptor.
			$d->close();
		}
		//
		// Returning a clean list of findings.
		return array_unique($out);
	}
	/**
	 * This method provides a way to get one or all image files matching
	 * certain name and extension.
	 *
	 * @param string $imageName Name of the file to look for (without
	 * extension).
	 * @param string $imageExtension File extension.
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function imagePath($imageName, $imageExtension, $full = false) {
		global $Paths;
		return $this->find($this->_imagesPaths, true, $Paths[GC_PATHS_IMAGES], $imageName, $imageExtension, $full);
	}
	/**
	 * This method provides a way to get one or all script files matching
	 * certain name.
	 *
	 * @param string $jsName Name of the file to look for (extension is
	 * assumed 'js').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function jsPath($jsName, $full = false) {
		global $Paths;
		return $this->find($this->_jsPaths, true, $Paths[GC_PATHS_JS], $jsName, self::ExtensionJS, $full);
	}
	/**
	 * This method allows to get a list of directories where non compiled
	 * language configuration files are stored.
	 *
	 * @return string[] List of directories (absolute paths).
	 */
	public function langNonBuiltPaths() {
		global $Paths;
		return $this->genPaths($Paths[GC_PATHS_LANGS]);
	}
	/**
	 * This method returns a list of language configuration files for certain
	 * language.
	 * If '$Defaults[GC_DEFAULTS_LANGS_BUILT]' is set to true, compiled
	 * version are to be used.
	 *
	 * @param string $lang
	 * @return string[] List of absolute paths.
	 */
	public function langPaths($lang) {
		//
		// Global dependencies.
		global $Paths;
		//
		// Avoiding multiple loads.
		if(!isset($this->_langPaths[$lang])) {
			//
			// Global dependencies.
			global $Defaults;
			global $Directories;
			//
			// Checking if compiled files are to be used or not.
			if($Defaults[GC_DEFAULTS_LANGS_BUILT]) {
				$this->_langPaths[$lang] = array();
				$this->_langPaths[$lang][] = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$Paths[GC_PATHS_LANGS]}");
			} else {
				$this->_langPaths[$lang] = $this->genPaths($Paths[GC_PATHS_LANGS]);
			}
		}
		//
		// Looking for files.
		$out = $this->find($this->_langPaths[$lang], false, $Paths[GC_PATHS_LANGS], $lang, self::ExtensionJSON, true);
		//
		// Returning found paths.
		return $out;
	}
	/**
	 * This method provides a way to get one or all model files matching
	 * certain name.
	 *
	 * @param string $modelName Name of the file to look for (extension is
	 * assumed 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function modelPath($modelName, $full = false) {
		global $Paths;
		return $this->find($this->_modelsPaths, false, $Paths[GC_PATHS_MODELS], $modelName, self::ExtensionPHP, $full);
	}
	/**
	 * This method allows to access the list of manifest files for each active
	 * module.
	 *
	 * @return string[string] Returns a list of absolute file paths associated
	 * with their module name.
	 */
	public function manifests() {
		return $this->_manifests;
	}
	/**
	 * This method provides access to the list of found modules.
	 *
	 * @return string[] List of active module names.
	 */
	public function modules() {
		return $this->_modules;
	}
	/**
	 * This method provides a way to get one or all table representation and
	 * factory files matching certain name.
	 *
	 * @param string $representationName Name of the file to look for
	 * (extension is assumed 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function representationPath($representationName, $full = false) {
		global $Paths;
		return $this->find($this->_representationPaths, false, $Paths[GC_PATHS_REPRESENTATIONS], $representationName, self::ExtensionPHP, $full);
	}
	/**
	 * This method returns the full list of routes configurations.
	 *
	 * @return string Returns a list of absolute paths.
	 */
	public function routesPaths() {
		global $Paths;
		return $this->find($this->_routesPaths, false, $Paths[GC_PATHS_CONFIGS], 'routes', self::ExtensionJSON, true);
	}
	/**
	 * This method provides a way to get one or all service files matching
	 * certain name.
	 *
	 * @param string $serviceName Name of the file to look for (extension is
	 * assumed 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function servicePath($serviceName, $full = false) {
		global $Paths;
		return $this->find($this->_servicePaths, false, $Paths[GC_PATHS_SERVICES], $serviceName, self::ExtensionPHP, $full);
	}
	/**
	 * This method provides a way to get one or all cron shell tool files
	 * matching certain name.
	 *
	 * @param string $name Name of the file to look for (extension is assumed
	 * 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function shellCron($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellCronsPaths, false, $Paths[GC_PATHS_SHELL_CRONS], $name, self::ExtensionPHP, $full);
	}
	/**
	 * This method provides a way to get one or all system shell tool files
	 * matching certain name.
	 *
	 * @param string $name Name of the file to look for (extension is assumed
	 * 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function shellSys($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellSysPaths, false, $Paths[GC_PATHS_SHELL_SYSTOOLS], $name, self::ExtensionPHP, $full);
	}
	/**
	 * This method provides a way to get one or all shell tool files matching
	 * certain name.
	 *
	 * @param string $name Name of the file to look for (extension is assumed
	 * 'php').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function shellTool($name, $full = false) {
		global $Paths;
		return $this->find($this->_shellToolsPaths, false, $Paths[GC_PATHS_SHELL_TOOLS], $name, self::ExtensionPHP, $full);
	}
	/**
	 * This method provides a way to get one or all snippet files matching
	 * certain name.
	 *
	 * @param string $snippetName Name of the file to look for (extension is
	 * assumed 'html').
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	public function snippetPath($snippetName, $full = false) {
		global $Paths;
		return $this->find($this->_snippetsPaths, true, $Paths[GC_PATHS_SNIPPETS], $snippetName, self::ExtensionTemplate, $full);
	}
	/**
	 * This method allows to get a list of directories where template files
	 * and folders are stored.
	 *
	 * @return string[] List of directory absolute paths.
	 */
	public function templateDirs() {
		if($this->_templatesPaths === false) {
			global $Paths;
			$this->_templatesPaths = $this->genPaths($Paths[GC_PATHS_TEMPLATES], true);
		}

		return $this->_templatesPaths;
	}
	/**
	 * This method allows to obtain the path of certain template.
	 *
	 * @param string $templateName Name to look for.
	 * @param string $mode Mode in which to look for.
	 * @return string Returns a template absolute path.
	 */
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
	/**
	 * This method generates a list of possible paths using a list of possible
	 * subfolders inside each module and 'ROOTDIR/site'.
	 * If there's a skin specified duplicates the list generating paths with
	 * and without the skin.
	 *
	 * @param string[] $folders List of subfolders.
	 * @param string $skin Skin name to consider. Ignored when false and
	 * current skin when true.
	 * @return string[] List of possible folders (absolute paths).
	 */
	protected function genPaths($folders, $skin = false) {
		//
		// Default values.
		$list = array();
		//
		// Global dependencies.
		global $Directories;
		//
		// If the skin is just a true boolean value, it's asking for the
		// current skin.
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
	/**
	 * This is the core method of this class and it's the one in charge of
	 * finding almost every requested path.
	 * 
	 * @param string[] $list of directories in which look for. When it's false
	 * it's automatically generated.
	 * @param string $skin Skin to have in consideration. It's usually a
	 * boolean true indication current skin.
	 * @param string[] $folders List of sub-folders to search in. It could
	 * also be a simple string and it can also contain asterisc. It's usually
	 * ignored except when '$list' is generated.
	 * @param string $name Name of the file to look for (without extension).
	 * @param string $extension File extension, by default 'php'.
	 * @param boolean $full Indicates if all found files has to be retunred or
	 * only one.
	 * @param boolean $asUri When true every path is transformed into an
	 * absolute URI before returning.
	 * @return mixed Returns an array with every path found or just one string
	 * when it's not set as full. All paths are absolute.
	 */
	protected function find(&$list, $skin, $folders, $name, $extension = false, $full = false, $asUri = false) {
		//
		// Default values.
		$out = array();
		$debugPaths = array();
		//
		// Checking if this name must be ignored. This provides a way to
		// disable file without removing them, just adding a dot or an
		// underscore to the beginning of it's name.
		$ignored = preg_match('/^([\._])(.*)/', $name);
		//
		// Checking if path debugs are active.
		$debugPathsActive = isset(Params::Instance()->debugpaths);
		//
		// If the list of directories is false, it must be generated.
		if($list === false) {
			$list = $this->genPaths($folders, $skin);
		}
		//
		// Cleaning file name.
		$filename = $name;
		if($extension) {
			$filename = "{$filename}.{$extension}";
		}
		//
		// Checking if this search must be ignored.
		if(!$ignored) {
			//
			// Looking inside each path on the list.
			foreach($list as $path) {
				//
				// Building the right path to look for.
				$filepath = Sanitizer::DirPath("{$path}/{$filename}");
				//
				// Loading files.
				foreach(glob($filepath) as $subpath) {
					//
					// Adding it as result only if it's
					// readable.
					if(is_readable($subpath)) {
						$out[] = $subpath;
					}
				}
			}
		}
		//
		// Generating information for debug reports.
		if($debugPathsActive) {
			$debugPaths['name'] = $name;
			$debugPaths['extension'] = $extension;
			$debugPaths['skin'] = $skin;
			$debugPaths['ignored'] = $ignored;
			$debugPaths['subfolders'] = $folders;
			$debugPaths['possibilities'] = $list;
		}
		//
		// Transforming paths into URI if requested.
		if($asUri) {
			foreach($out as &$v) {
				$v = self::Path2Uri($v);
			}
		}
		//
		// All or just one?
		if(!$full) {
			$out = count($out) > 0 ? array_shift($out) : false;
		}
		//
		// Displaying debug report.
		if($debugPathsActive) {
			$debugPaths['result'] = $out;

			\TooBasic\debugThing($debugPaths);
		}
		//
		// Returning findings.
		return $out;
	}
	/**
	 * This method loads the list of active modules.
	 */
	protected function loadModules() {
		//
		// Avoiding multiple loads.
		if($this->_modules === false) {
			//
			// Global dependencies.
			global $Directories;
			//
			// Default values.
			$this->_modules = array();
			$this->_manifests = array();
			//
			// Loading all possible paths.
			foreach(glob("{$Directories[GC_DIRECTORIES_MODULES]}/*", GLOB_ONLYDIR) as $pathname) {
				//
				// Obtaining path information
				$path = pathinfo($pathname);
				//
				// Checking conditions to ignore a module:
				//	- starts with an underscore.
				//	- starts with a dot.
				//	- has a file called '.inactive'.
				if(!preg_match('/^([\._])(.*)/', $path['filename']) && !is_readable("{$pathname}/.inactive")) {
					//
					// Adding path as a known module.
					$this->_modules[] = $path['filename'];
					//
					// Setting a default manifest.
					$this->_manifests[$path['filename']] = false;
					//
					// If there IS a readable manifest file,
					// it's path is stored.
					if(is_readable("{$pathname}/manifest.json")) {
						$this->_manifests[$path['filename']] = "{$pathname}/manifest.json";
					}
				}
			}
		}
	}
	//
	// Public class methods.
	/**
	 * This method cleans a full path removing the DocumentRoot and retruning
	 * it as an absolute URI.
	 * Warning: if '$path' is not inside the DocumentRoot, this method may not
	 * be what you need.
	 *
	 * @param string $path Path to convert into a URI.
	 * @return string Returns an absolute URI.
	 */
	public static function Path2Uri($path) {
		return Sanitizer::UriPath(substr($path, strlen(Params::Instance()->server->DOCUMENT_ROOT)));
	}
}
