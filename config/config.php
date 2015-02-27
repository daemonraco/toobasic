<?php

define("ROOTDIR", dirname(__DIR__));
define("ROOTURI", dirname($_SERVER["SCRIPT_NAME"]));

require_once ROOTDIR."/includes/Sanitizer.php";

//
// Default constants configurations.
$Defaults = array();
//
// Directory configurations.
$Directories = array();
$Directories["includes"] = Sanitizer::DirPath(ROOTDIR."/includes");
$Directories["managers"] = Sanitizer::DirPath("{$Directories["includes"]}/managers");
//
// Directory configurations.
$Uris = array();
$Uris["includes"] = Sanitizer::UriPath(ROOTURI."/includes");

require_once __DIR__."/loader.php";

$ActionName = isset($_REQUEST["action"]) ? $_REQUEST["action"] : false;
$ServiceName = isset($_REQUEST["service"]) ? $_REQUEST["service"] : false;
