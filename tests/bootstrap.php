<?php

//
// Loading composer autoload.
require_once dirname(__DIR__).'/vendor/autoload.php';
//
// Loading TooBasic test assets assets.
require_once __DIR__.'/assets/autoload.php';
//
// Loading some generic and useful functions.
require_once TESTS_ROOTDIR.'/includes/corefunctions.php';

if(defined('TRAVISCI_VERBOSE')) {
	TooBasic_AssetsManager::$Verbose = true;
}
