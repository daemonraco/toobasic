<?php

spl_autoload_register(function($class) {
	$classExpanded = explode("\\", $class);
	$class = array_pop($classExpanded);
	$namespace = implode("\\", $classExpanded);

	if($namespace == "TooBasic") {
		global $Directories;

		$path = false;

		static $basicIncludes = array(
			"Adapter",
			"Controller",
			"ErrorController",
			"Exporter",
			"Layout",
			"Model",
			"ModelsFactory",
			"Params",
			"ParamsStack",
			"Paths",
			"Service",
			"Singleton",
			"Translate"
		);
		static $managersIncludes = array(
			"ActionsManager",
			"Manager",
			"ServicesManager",
			"ShellManager",
			"UrlManager"
		);
		static $cacheAdapters = array(
			"CacheAdapter",
			"CacheAdapterFile"
		);
		static $viewAdapters = array(
			"ViewAdapter",
			"ViewAdapterJSON",
			"ViewAdapterSmarty"
		);

		if(!$path) {
			if(in_array($class, $basicIncludes)) {
				$path = TooBasic\Sanitizer::DirPath("{$Directories["includes"]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $managersIncludes)) {
				$path = TooBasic\Sanitizer::DirPath("{$Directories["managers"]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $cacheAdapters)) {
				$path = TooBasic\Sanitizer::DirPath("{$Directories["adapters-cache"]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $viewAdapters)) {
				$path = TooBasic\Sanitizer::DirPath("{$Directories["adapters-view"]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}

		if($path) {
			require_once $path;
		}
	}
});
//
// Shell includes
spl_autoload_register(function($class) {
	$classExpanded = explode("\\", $class);
	$class = array_pop($classExpanded);
	$namespace = implode("\\", $classExpanded);

	if($namespace == "TooBasic\Shell") {
		$path = false;

		global $Directories;

		static $basicIncludes = array(
			"Option",
			"Options"
		);

		if(!$path) {
			if(in_array($class, $basicIncludes)) {
				$path = TooBasic\Sanitizer::DirPath("{$Directories["shell-includes"]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}

		if($path) {
			require_once $path;
		}
	}
});
//
// Known librearies
spl_autoload_register(function($class) {
	$path = false;

	global $Directories;

	if(!$path && $class == "Smarty") {
		$path = TooBasic\Sanitizer::DirPath("{$Directories["libraries"]}/smarty/Smarty.class.php");
		$path = is_readable($path) ? $path : false;
	}

	if($path) {
		require_once $path;
	}
});
