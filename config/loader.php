<?php

spl_autoload_register(function($class) {
	$classExpanded = explode('\\', $class);
	$class = array_pop($classExpanded);
	$namespace = implode('\\', $classExpanded);

	if($namespace == 'TooBasic') {
		global $Directories;

		$path = false;

		static $basicIncludes = array(
			'Adapter',
			'Controller',
			'ControllerExports',
			'ErrorController',
			'Exporter',
			'Layout',
			'MagicProp',
			'Model',
			'ModelsFactory',
			'Params',
			'ParamsStack',
			'Paths',
			'Service',
			'Singleton',
			'Timer',
			'Translate'
		);
		static $managersIncludes = array(
			'ActionsManager',
			'DBManager',
			'DBStructureManager',
			'Manager',
			'RoutesManager',
			'ServicesManager',
			'ShellManager',
			'UrlManager'
		);
		static $cacheAdapters = array(
			'CacheAdapter',
			'CacheAdapterDB',
			'CacheAdapterDBMySQL',
			'CacheAdapterFile',
			'CacheAdapterMemcache',
			'CacheAdapterMemcached',
			'CacheAdapterNoCache'
		);
		static $dbAdapters = array(
			'DBAdapter',
			'DBSpecAdapter',
			'DBSpecAdapterMySQL',
			'DBSpecAdapterSQLite'
		);
		static $viewAdapters = array(
			'ViewAdapter',
			'ViewAdapterBasic',
			'ViewAdapterDump',
			'ViewAdapterJSON',
			'ViewAdapterPrint',
			'ViewAdapterSerialize',
			'ViewAdapterSmarty',
			'ViewAdapterXML'
		);
		static $representations = array(
			'ItemRepresentation',
			'ItemsFactory',
			'ItemsFactoryProvider'
		);

		if(!$path) {
			if(in_array($class, $basicIncludes)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_INCLUDES]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $managersIncludes)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MANAGERS]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $cacheAdapters)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_ADAPTERS_CACHE]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $dbAdapters)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_ADAPTERS_DB]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $viewAdapters)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_ADAPTERS_VIEW]}/{$class}.php");
				$path = is_readable($path) ? $path : false;
			}
		}
		if(!$path) {
			if(in_array($class, $representations)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_REPRESENTATIONS]}/{$class}.php");
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
	$classExpanded = explode('\\', $class);
	$class = array_pop($classExpanded);
	$namespace = implode('\\', $classExpanded);

	if($namespace == 'TooBasic\\Shell') {
		$path = false;

		global $Directories;

		static $basicIncludes = array(
			'Color',
			'Option',
			'Options',
			'ShellCron',
			'ShellTool'
		);

		if(!$path) {
			if(in_array($class, $basicIncludes)) {
				$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SHELL_INCLUDES]}/{$class}.php");
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
		$path = \TooBasic\Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_LIBRARIES]}/smarty/Smarty.class.php");
		$path = is_readable($path) ? $path : false;
	}

	if($path) {
		require_once $path;
	}
});
