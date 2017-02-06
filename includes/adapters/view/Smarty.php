<?php

/**
 * @file Smarty.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\View;

//
// Class aliases.
use \TooBasic\Sanitizer;

/**
 * @class Smarty
 * This view adapter provides the possibility of rendering output through 'Smarty'
 * and all it's functionalities.
 */
class Smarty extends Adapter {
	//
	// Constants.
	const SmartyStuffDirectory = '/smarty';
	const SmartyCompileDirectory = '/smarty/compile';
	const SmartyCacheDirectory = '/smarty/cache';
	const SmartyConfigDirectory = '/smarty/configs';
	//
	// Protected properties.
	/**
	 * @var string[] List of directories where a template can be found.
	 */
	protected $_templateDirs = false;
	/**
	 * @var \Smarty Shortcut to the Smarty's object.
	 */
	protected $_smarty = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Checking the existence and permission of required directories.
		$this->checkDirectories();
		//
		// Global dependencies.
		global $Defaults;
		global $Directories;
		//
		// Creating a Smarty's object and saving a shortcut to it.
		$this->_smarty = new \Smarty();
		//
		// Setting specific delimiters.
		if($Defaults[GC_SMARTY_LEFT_DELIMITER] !== false && $Defaults[GC_SMARTY_RIGHT_DELIMITER] !== false) {
			$this->_smarty->left_delimiter = $Defaults[GC_SMARTY_LEFT_DELIMITER];
			$this->_smarty->right_delimiter = $Defaults[GC_SMARTY_RIGHT_DELIMITER];
		}
		//
		// Setting template directories.
		foreach($this->_templateDirs as $path) {
			$this->_smarty->addTemplateDir($path);
		}
		//
		// Setting special Smarty directories.
		$this->_smarty->setCompileDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyCompileDirectory));
		$this->_smarty->setConfigDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyConfigDirectory));
		$this->_smarty->setCacheDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyCacheDirectory));

		$this->_smarty->assign('app_name', __CLASS__);
	}
	//
	// Public methods.
	/**
	 * This method provides access to the Smarty's object in use.
	 *
	 * @return \Smarty Returns a shortcut to Smarty's object.
	 */
	public function engine() {
		return $this->_smarty;
	}
	/**
	 * This method is the one in charge of rendering the output using a list
	 * of assignments and retruning it for further process.
	 *
	 * @param mixed[string] $assignments List of assignments to be analysed.
	 * @param string $template Name of the template to use while rendering.
	 * @return string Retruns a view rendering result.
	 */
	public function render($assignments, $template) {
		//
		// Including this class' auto assignments.
		$merge = array_merge($this->_autoAssigns, $assignments);
		//
		// Exporting each assignment into Smarty.
		foreach($merge as $key => $value) {
			$this->_smarty->assign($key, $value);
		}
		//
		// Rendering an output result based on the given template name.
		return $this->_smarty->fetch($template);
	}
	//
	// Protected methods.
	/**
	 * This method checks the existence and permissions of certain required
	 * directories and files.
	 */
	protected function checkDirectories() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// This checks are preformed whenever this site is not flagged as
		// installed.
		if(!$Defaults[GC_DEFAULTS_INSTALLED]) {
			//
			// Global dependencies.
			global $Directories;
			//
			// Checking the existence of Smarty's special directories.
			foreach([self::SmartyStuffDirectory, self::SmartyCacheDirectory, self::SmartyCompileDirectory, self::SmartyConfigDirectory] as $subPath) {
				$dirPath = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$subPath}");
				if(!is_dir($dirPath)) {
					mkdir($dirPath, $Defaults[GC_DEFAULTS_CACHE_PERMISSIONS], true);
				}
			}
			//
			// TooBasic creates a '.htaccess' file special for smarty
			// required to avoid some attacks.
			$htaccess = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyStuffDirectory.'/.htaccess');
			if(!is_file($htaccess)) {
				//
				// Creating a basic '.htaccess' file to forbid
				// direct access to any file inside Smarty's
				// special directories.
				$rules = "<Files \"*\">\n";
				$rules.= "    Order Deny,Allow\n";
				$rules.= "    Deny from all\n";
				$rules.= "</Files>\n";

				file_put_contents($htaccess, $rules);
			}
		}
	}
}
