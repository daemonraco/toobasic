<?php

//
// Detecting current root directory.
define("ROOTDIR", dirname(__DIR__));
//
// Detecting command line access.
if(php_sapi_name() == "cli") {
	define("__SHELL__", php_sapi_name());
}
//
// Detecting current URI.
define("ROOTURI", !defined("__SHELL__") ? dirname($_SERVER["SCRIPT_NAME"]) : false);
//
// Detected mobile access.
if(!defined("__SHELL__") && isset($_SERVER["HTTP_USER_AGENT"]) && preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER["HTTP_USER_AGENT"])) {
	define("__IS_MOBILE__", true);
}
//
// Basic requirements.
require_once __DIR__."/define.php";
require_once ROOTDIR."/includes/corefunctions.php";
require_once ROOTDIR."/includes/Sanitizer.php";
//
// Default constants configurations.
$Defaults = array();
$Defaults["action"] = "home";
$Defaults["cache-adapter"] = "TooBasic\CacheAdapterFile";
$Defaults["cache-permissions"] = 0777;
$Defaults["installed"] = false;
$Defaults["langs-defaultlang"] = "en_us";
$Defaults["layout"] = false;
$Defaults["langs-built"] = false;
$Defaults["service"] = "";
$Defaults["view-adapter"] = "TooBasic\ViewAdapterSmarty";
//
// Directory configurations.
$Directories = array();
$Directories["cache"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/cache");
$Directories["configs"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/config");
$Directories["includes"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/includes");
$Directories["libraries"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/libraries");
$Directories["managers"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/includes/managers");
$Directories["adapters"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/includes/adapters");
$Directories["adapters-cache"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/includes/adapters/cache");
$Directories["adapters-view"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/includes/adapters/view");
$Directories["modules"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/modules");
$Directories["shell"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/shell");
$Directories["shell-includes"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/shell/includes");
$Directories["site"] = TooBasic\Sanitizer::DirPath(ROOTDIR."/site");
//
// Directory configurations.
$Uris = array();
$Uris["includes"] = TooBasic\Sanitizer::UriPath(ROOTURI."/includes");
//
// Paths configurations.
$Paths = array();
$Paths["configs"] = "/configs";
$Paths["controllers"] = "/controllers";
$Paths["css"] = "/styles";
$Paths["js"] = "/scripts";
$Paths["langs"] = "/langs";
$Paths["layouts"] = "/layouts";
$Paths["models"] = "/models";
$Paths["shell"] = "/shell";
$Paths["templates"] = "/templates";

require_once __DIR__."/loader.php";
//
// Local configuration.
$localConfig = TooBasic\Sanitizer::DirPath("{$Directories["site"]}/config.php");
if(is_readable($localConfig)) {
	require_once $localConfig;
}

$ActionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
$ServiceName = isset($_REQUEST["service"]) ? $_REQUEST["service"] : false;
$ModeName = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : false;
