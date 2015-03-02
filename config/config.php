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
//
// Directory configurations.
$Directories = array();
$Directories["includes"] = Sanitizer::DirPath(ROOTDIR."/includes");
$Directories["managers"] = Sanitizer::DirPath("{$Directories["includes"]}/managers");
$Directories["models"] = Sanitizer::DirPath("{$Directories["includes"]}/models");
$Directories["modules"] = Sanitizer::DirPath(ROOTDIR."/modules");
$Directories["site"] = Sanitizer::DirPath(ROOTDIR."/site");
//
// Directory configurations.
$Uris = array();
$Uris["includes"] = Sanitizer::UriPath(ROOTURI."/includes");
//
// Paths configurations
$Paths = array();
$Paths["controllers"] = "/controllers";
$Paths["models"] = "/models";

require_once __DIR__."/loader.php";

$ActionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
$ServiceName = isset($_REQUEST["service"]) ? $_REQUEST["service"] : false;
