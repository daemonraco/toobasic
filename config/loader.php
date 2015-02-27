<?php

spl_autoload_register(function($class) {
	$found = false;

	global $Directories;

	$basicIncludes = array(
		"Controller",
		"Paths",
		"Service",
		"Singleton"
	);
	$managersIncludes = array(
		"ActionsManager",
		"ServicesManager"
	);

	if(!$found) {
		if(in_array($class, $basicIncludes)) {
			$path = Sanitizer::DirPath("{$Directories["includes"]}/{$class}.php");
			if(is_readable($path)) {
				require_once $path;
				$found = true;
			}
		}
	}

	if(!$found) {
		if(in_array($class, $managersIncludes)) {
			$path = Sanitizer::DirPath("{$Directories["managers"]}/{$class}.php");
			if(is_readable($path)) {
				require_once $path;
				$found = true;
			}
		}
	}
});
