<?php

spl_autoload_register(function($class) {
	$path = false;

	global $Directories;

	$basicIncludes = array(
		"Adapter",
		"Controller",
		"Exporter",
		"Model",
		"ModelsFactory",
		"Paths",
		"Service",
		"Singleton"
	);
	$managersIncludes = array(
		"ActionsManager",
		"Manager",
		"ServicesManager",
		"UrlManager"
	);
	$cacheAdapters = array(
		"CacheAdapter",
		"CacheAdapterFile"
	);
	$viewAdapters = array(
		"ViewAdapter",
		"ViewAdapterSmarty"
	);

	if(!$path) {
		if(in_array($class, $basicIncludes)) {
			$path = Sanitizer::DirPath("{$Directories["includes"]}/{$class}.php");
			$path = is_readable($path) ? $path : false;
		}
	}
	if(!$path) {
		if(in_array($class, $managersIncludes)) {
			$path = Sanitizer::DirPath("{$Directories["managers"]}/{$class}.php");
			$path = is_readable($path) ? $path : false;
		}
	}
	if(!$path) {
		if(in_array($class, $cacheAdapters)) {
			$path = Sanitizer::DirPath("{$Directories["adapters-cache"]}/{$class}.php");
			$path = is_readable($path) ? $path : false;
		}
	}
	if(!$path) {
		if(in_array($class, $viewAdapters)) {
			$path = Sanitizer::DirPath("{$Directories["adapters-view"]}/{$class}.php");
			$path = is_readable($path) ? $path : false;
		}
	}

	if($path) {
		require_once $path;
	}
});
//
// Known librearies
spl_autoload_register(function($class) {
	$path = false;

	global $Directories;

	if(!$path && $class == "Smarty") {
		$path = Sanitizer::DirPath("{$Directories["libraries"]}/smarty/Smarty.class.php");
		$path = is_readable($path) ? $path : false;
	}

	if($path) {
		require_once $path;
	}
});
