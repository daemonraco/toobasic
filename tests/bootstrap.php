<?php

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

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once __DIR__.'/TooBasic_TestCase.php';
require_once dirname(__DIR__).'/config/config.php';
// @fixme remove this travis test @{
debugit(array(
	'PHP_VERSION' => PHP_VERSION,
	'phpversion()' > phpversion()
));
// @}
