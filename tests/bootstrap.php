<?php

//
// Loading TravisCI variables.
echo "Loading environment variables:\n";
$aux = $_ENV;
ksort($aux);
foreach($aux as $name => $value) {
	if(preg_match('/^TRAVISCI_/', $name)) {
		echo "\t- '\033[1;36m{$name}\033[0m' (value length: \033[1;33m".strlen($value)."\033[0m)\n";
		define($name, $value);
	}
}
unset($aux);
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
