<?php

use \TooBasic\Sanitizer as TB_Sanitizer;

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
require_once ROOTDIR."/includes/toobasicfunctions.php";
require_once ROOTDIR."/includes/Sanitizer.php";
//
// Default constants configurations.
$Defaults = array();
$Defaults[GC_DEFAULTS_ACTION] = "home";
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = "\TooBasic\CacheAdapterFile";
$Defaults[GC_DEFAULTS_CACHE_PERMISSIONS] = 0777;
$Defaults[GC_DEFAULTS_INSTALLED] = false;
$Defaults[GC_DEFAULTS_LANGS_DEFAULTLANG] = "en_us";
$Defaults[GC_DEFAULTS_LAYOUT] = false;
$Defaults[GC_DEFAULTS_LANGS_BUILT] = false;
$Defaults[GC_DEFAULTS_SERVICE] = "";
$Defaults[GC_DEFAULTS_VIEW_ADAPTER] = "\TooBasic\ViewAdapterSmarty";
//
// Directory configurations.
$Directories = array();
$Directories[GC_DIRECTORIES_CACHE] = TB_Sanitizer::DirPath(ROOTDIR."/cache");
$Directories[GC_DIRECTORIES_CONFIGS] = TB_Sanitizer::DirPath(ROOTDIR."/config");
$Directories[GC_DIRECTORIES_INCLUDES] = TB_Sanitizer::DirPath(ROOTDIR."/includes");
$Directories[GC_DIRECTORIES_LIBRARIES] = TB_Sanitizer::DirPath(ROOTDIR."/libraries");
$Directories[GC_DIRECTORIES_MANAGERS] = TB_Sanitizer::DirPath(ROOTDIR."/includes/managers");
$Directories[GC_DIRECTORIES_ADAPTERS] = TB_Sanitizer::DirPath(ROOTDIR."/includes/adapters");
$Directories[GC_DIRECTORIES_ADAPTERS_CACHE] = TB_Sanitizer::DirPath(ROOTDIR."/includes/adapters/cache");
$Directories[GC_DIRECTORIES_ADAPTERS_DB] = TB_Sanitizer::DirPath(ROOTDIR."/includes/adapters/db");
$Directories[GC_DIRECTORIES_ADAPTERS_VIEW] = TB_Sanitizer::DirPath(ROOTDIR."/includes/adapters/view");
$Directories[GC_DIRECTORIES_MODULES] = TB_Sanitizer::DirPath(ROOTDIR."/modules");
$Directories[GC_DIRECTORIES_SHELL] = TB_Sanitizer::DirPath(ROOTDIR."/shell");
$Directories[GC_DIRECTORIES_SHELL_INCLUDES] = TB_Sanitizer::DirPath(ROOTDIR."/shell/includes");
$Directories[GC_DIRECTORIES_SITE] = TB_Sanitizer::DirPath(ROOTDIR."/site");
//
// Connections.
$Connections = array();
//
// Conection specs:
// $Connections[GC_CONNECTIONS_DB]["<name>"] = array(
//      GC_CONNECTIONS_DB_ENGINE   => "", // Something like: "mysqli"
//      GC_CONNECTIONS_DB_SERVER   => "", // IP or server address.
//      GC_CONNECTIONS_DB_PORT     => "", // false when defatul.
//      GC_CONNECTIONS_DB_NAME     => "", // Database name or schema.
//      GC_CONNECTIONS_DB_USERNAME => "",
//      GC_CONNECTIONS_DB_PASSWORD => "",
//      GC_CONNECTIONS_DB_PREFIX   => false  // optiona.
//      GC_CONNECTIONS_DB_SID      => false  // for Oracle only.
// );
// @{
$Connections[GC_CONNECTIONS_DB] = array();
$Connections[GC_CONNECTIONS_DEFAUTLS] = array();
$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB] = false;
//
// Directory configurations.
$Uris = array();
$Uris[GC_URIS_INCLUDES] = TB_Sanitizer::UriPath(ROOTURI."/includes");
//
// Paths configurations.
$Paths = array();
$Paths[GC_PATHS_CONFIGS] = "/configs";
$Paths[GC_PATHS_CONTROLLERS] = "/controllers";
$Paths[GC_PATHS_SERVICES] = "/services";
$Paths[GC_PATHS_CSS] = "/styles";
$Paths[GC_PATHS_JS] = "/scripts";
$Paths[GC_PATHS_LANGS] = "/langs";
$Paths[GC_PATHS_LAYOUTS] = "/layouts";
$Paths[GC_PATHS_MODELS] = "/models";
$Paths[GC_PATHS_SHELL] = "/shell";
$Paths[GC_PATHS_SHELL_CRONS] = "/shell/crons";
$Paths[GC_PATHS_SHELL_TOOLS] = "/shell/tools";
$Paths[GC_PATHS_TEMPLATES] = "/templates";

require_once __DIR__."/loader.php";
//
// Local configuration.
$localConfig = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
if(is_readable($localConfig)) {
	require_once $localConfig;
}

$ActionName = isset($_REQUEST[GC_REQUEST_ACTION]) ? $_REQUEST[GC_REQUEST_ACTION] : $Defaults["action"];
$LayoutName = isset($_REQUEST[GC_REQUEST_LAYOUT]) ? $_REQUEST[GC_REQUEST_LAYOUT] : $Defaults["layout"];
$ServiceName = isset($_REQUEST[GC_REQUEST_SERVICE]) ? $_REQUEST[GC_REQUEST_SERVICE] : false;
$ModeName = isset($_REQUEST[GC_REQUEST_MODE]) ? $_REQUEST[GC_REQUEST_MODE] : false;
