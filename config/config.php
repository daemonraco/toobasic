<?php

define("ROOTDIR", dirname(__DIR__));
define("ROOTURI", dirname($_SERVER["SCRIPT_NAME"]));

require_once ROOTDIR."/includes/corefunctions.php";
require_once ROOTDIR."/includes/Sanitizer.php";

//
// Default constants configurations.
$Defaults = array();
$Defaults["action"] = "home";
$Defaults["service"] = "";
$Defaults["cache-adapter"] = "CacheAdapterFile";
//
// Directory configurations.
$Directories = array();
$Directories["cache"] = Sanitizer::DirPath(ROOTDIR."/cache");
$Directories["configs"] = Sanitizer::DirPath(ROOTDIR."/config");
$Directories["includes"] = Sanitizer::DirPath(ROOTDIR."/includes");
$Directories["managers"] = Sanitizer::DirPath(ROOTDIR."/includes/managers");
$Directories["adapters"] = Sanitizer::DirPath(ROOTDIR."/includes/adapters");
$Directories["adapters-cache"] = Sanitizer::DirPath(ROOTDIR."/includes/adapters/cache");
$Directories["modules"] = Sanitizer::DirPath(ROOTDIR."/modules");
$Directories["site"] = Sanitizer::DirPath(ROOTDIR."/site");
//
// Directory configurations.
$Uris = array();
$Uris["includes"] = Sanitizer::UriPath(ROOTURI."/includes");
//
// Paths configurations
$Paths = array();
$Paths["configs"] = "/configs";
$Paths["controllers"] = "/controllers";
$Paths["layouts"] = "/layouts";
$Paths["models"] = "/models";
$Paths["js"] = "/scripts";
$Paths["css"] = "/styles";

require_once __DIR__."/loader.php";

$ActionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
$ServiceName = isset($_REQUEST["service"]) ? $_REQUEST["service"] : false;
