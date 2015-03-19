<?php

namespace TooBasic;

class ViewAdapterSmarty extends ViewAdapter {
	//
	// Constants.
//	const SmartyTemplatesDirectory="/smarty/";
	const SmartyStuffDirectory = "/smarty";
	const SmartyCompileDirectory = "/smarty/compile";
	const SmartyCacheDirectory = "/smarty/cache";
	const SmartyConfigDirectory = "/smarty/configs";
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
		$this->_smarty->setCompileDir(Sanitizer::DirPath("{$Directories["cache"]}/".self::SmartyCompileDirectory));
		$this->_smarty->setConfigDir(Sanitizer::DirPath("{$Directories["cache"]}/".self::SmartyConfigDirectory));
		$this->_smarty->setCacheDir(Sanitizer::DirPath("{$Directories["cache"]}/".self::SmartyCacheDirectory));

//		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->_smarty->assign("app_name", __CLASS__);
	}
	//
	// Public methods.
	public function render($assignments, $template = false) {
		foreach($this->_autoAssigns as $key => $value) {
			$this->_smarty->assign($key, $value);
		}
		foreach($assignments as $key => $value) {
			$this->_smarty->assign($key, $value);
		}

		return $this->_smarty->fetch($template);
	}
	//
	// Protected methods.
	protected function checkDirectories() {
		global $Defaults;

		if(!$Defaults["installed"]) {
			global $Directories;

			foreach(array(self::SmartyStuffDirectory, self::SmartyCacheDirectory, self::SmartyCompileDirectory, self::SmartyConfigDirectory) as $subPath) {
				$dirPath = Sanitizer::DirPath("{$Directories["cache"]}/{$subPath}");
				if(!is_dir($dirPath)) {
					mkdir($dirPath, $Defaults["cache-permissions"], true);
				}
			}

			$htaccess = Sanitizer::DirPath("{$Directories["cache"]}/".self::SmartyStuffDirectory."/.htaccess");
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
