<?php

spl_autoload_register(function($class) {
	$classExpanded = explode("\\", $class);
	$namespace = array_shift($classExpanded);

	if($namespace == "TooBasic") {
		$class = array_pop($classExpanded);

		global $Directories;

		$path = false;

		$basicIncludes = array(
			"Adapter",
			"Controller",
			"ErrorController",
			"Exporter",
			"Model",
			"ModelsFactory",
			"Params",
			"ParamsStack",
			"Paths",
			"Service",
			"Singleton",
			"Translate"
		);
		$managersIncludes = array(
			"ActionsManager",
			"Manager",
			"ServicesManager",
			"ShellManager",
			"UrlManager"
		);
		$cacheAdapters = array(
			"CacheAdapter",
			"CacheAdapterFile"
		);
		$viewAdapters = array(
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
	$namespace = array_shift($classExpanded);

	if($namespace == "TooBasic") {
		$class = array_pop($classExpanded);

		$path = false;

		global $Directories;

		$basicIncludes = array(
			"XXXXXXX"
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
