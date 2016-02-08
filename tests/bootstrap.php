<?php

//
// Loading TravisCI variables.
foreach($_ENV as $name => $value) {
	if(preg_match('/^TRAVISCI_/', $name)) {
		define($name, $value);
	}
}
//
// Basic constants.
define('TESTS_ROOTDIR', dirname(__DIR__));
define('TESTS_ROOTURI', php_sapi_name() != 'cli' ? dirname($_SERVER['SCRIPT_NAME']) : false);
//
// Loading composer autoload.
require_once dirname(__DIR__).'/vendor/autoload.php';
//
// Loading TooBasic test assets assets
require_once __DIR__.'/assets/autoload.php';
//
// Global dependencies.
global $ActionName;
global $argc;
global $argv;
global $Connections;
global $CronProfiles;
global $Database;
global $Defaults;
global $Directories;
global $LanguageName;
global $LayoutName;
global $MagicProps;
global $ModeName;
global $Paths;
global $SApiReader;
global $Search;
global $ServiceName;
global $SkinName;
global $SuperLoader;
//
// Loading TooBasic main config file.
require_once dirname(__DIR__).'/config/config.php';
