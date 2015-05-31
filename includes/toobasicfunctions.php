<?php

namespace TooBasic;

function classname($simpleName) {
	$out = $simpleName;

	$out = str_replace(array("_", "-", ":"), " ", $out);
	$out = ucwords($out);
	$out = str_replace(" ", "", $out);

	return $out;
}
function guessSkin() {
	global $Defaults;
	global $SkinName;

	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');

	$SkinName = isset($_REQUEST[GC_REQUEST_SKIN]) ? $_REQUEST[GC_REQUEST_SKIN] : (isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : $Defaults[GC_DEFAULTS_SKIN]);
	//
	// Skin debugs.
	if(isset($_REQUEST['debugskin'])) {
		echo "<pre style=\"border:dashed gray 1px;width:100%;padding:5px;\">\n";
		echo "Current Skin: '{$SkinName}'\n\n";
		echo 'Default Skin: '.($Defaults[GC_DEFAULTS_SKIN] ? "'{$Defaults[GC_DEFAULTS_SKIN]}'" : 'Not set')."\n";
		echo 'Session Skin: '.(isset($_SESSION[$sessionKey]) ? "'{$_SESSION[$sessionKey]}'" : 'Not set')."\n";
		echo 'URL Skin:     '.(isset($_REQUEST[GC_REQUEST_SKIN]) ? "'{$_REQUEST[GC_REQUEST_SKIN]}'" : 'Not set')."\n";
		echo '</pre>';
		die;
	}

	return $SkinName;
}
function objectCopyAndEnforce($fields, \stdClass $origin, \stdClass $destination, $defualt = array()) {
	if(!is_array($defualt)) {
		$defualt = array();
	}

	foreach($fields as $field) {
		if(isset($origin->{$field})) {
			$destination->{$field} = $origin->{$field};
		} elseif(!isset($destination->{$field})) {
			$destination->{$field} = isset($defualt[$field]) ? $defualt[$field] : "";
		}
	}

	return $destination;
}
function setSessionSkin($name) {
	global $Defaults;

	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');

	if($name) {
		$_SESSION[$sessionKey] = $name;
	} elseif(isset($_SESSION[$sessionKey])) {
		unset($_SESSION[$sessionKey]);
	}
}
