<?php

/**
 * @file ViewAdapterSmarty.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ViewAdapterSmarty
 */
class ViewAdapterSmarty extends ViewAdapter {
	//
	// Constants.
//	const SmartyTemplatesDirectory = "/smarty/";
	const SmartyStuffDirectory = '/smarty';
	const SmartyCompileDirectory = '/smarty/compile';
	const SmartyCacheDirectory = '/smarty/cache';
	const SmartyConfigDirectory = '/smarty/configs';
	//
	// Protected properties.
	protected $_templateDirs = false;
	protected $_smarty = false;
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->checkDirectories();

		global $Directories;

		$this->_smarty = new \Smarty();

		foreach($this->_templateDirs as $path) {
			$this->_smarty->addTemplateDir($path);
		}
		$this->_smarty->setCompileDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyCompileDirectory));
		$this->_smarty->setConfigDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyConfigDirectory));
		$this->_smarty->setCacheDir(Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyCacheDirectory));

//		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->_smarty->assign('app_name', __CLASS__);
	}
	//
	// Public methods.
	public function engine() {
		return $this->_smarty;
	}
	public function render($assignments, $template) {
		foreach($assignments as $key => $value) {
			$this->_smarty->assign($key, $value);
		}

		return $this->_smarty->fetch($template);
	}
	//
	// Protected methods.
	protected function checkDirectories() {
		global $Defaults;

		if(!$Defaults[GC_DEFAULTS_INSTALLED]) {
			global $Directories;

			foreach(array(self::SmartyStuffDirectory, self::SmartyCacheDirectory, self::SmartyCompileDirectory, self::SmartyConfigDirectory) as $subPath) {
				$dirPath = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$subPath}");
				if(!is_dir($dirPath)) {
					mkdir($dirPath, $Defaults[GC_DEFAULTS_CACHE_PERMISSIONS], true);
				}
			}

			$htaccess = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/".self::SmartyStuffDirectory.'/.htaccess');
			if(!is_file($htaccess)) {
				$rules = "<Files \"*\">\n";
				$rules.= "    Order Deny,Allow\n";
				$rules.= "    Deny from all\n";
				$rules.= "</Files>\n";

				file_put_contents($htaccess, $rules);
			}
		}
	}
}
