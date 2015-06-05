<?php

namespace TooBasic;

const DebugThingTypeOk = 'ok';
const DebugThingTypeError = 'error';
const DebugThingTypeWarning = 'warning';
/**
 * @todo doc
 */
function checkBasicPermissions() {
	global $Directories;

	$writableDirectories = array(
		$Directories[GC_DIRECTORIES_CACHE],
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/filecache"),
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/langs"),
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/shellflags")
	);
	foreach($writableDirectories as $path) {
		if(!is_dir($path)) {
			debugThing("'{$path}' is not a directory", \TooBasic\DebugThingTypeError);
			die;
		}
		if(!is_writable($path)) {
			debugThing("'{$path}' is not writable", \TooBasic\DebugThingTypeError);
			die;
		}
	}
}
function classname($simpleName) {
	$out = $simpleName;

	$out = str_replace(array("_", "-", ":"), " ", $out);
	$out = ucwords($out);
	$out = str_replace(" ", "", $out);

	return $out;
}
function debugThing($thing, $type = \TooBasic\DebugThingTypeOk, $title = null) {
	//
	// Storing data displayed in a buffer for post processing.
	ob_start();
	//
	// 
	if(is_bool($thing)) {
		echo (boolval($thing) ? "true" : "false")."\n";
	} elseif(is_null($thing)) {
		echo "NULL\n";
	} elseif(is_callable($thing)) {
		$thing();
	} elseif(is_object($thing) || is_array($thing)) {
		print_r($thing);
	} else {
		echo "{$thing}\n";
	}

	$out = ob_get_contents();
	ob_end_clean();

	if(defined("__SHELL__")) {
		$out = explode("\n", $out);
		array_walk($out, function(& $item) {
			$item = "| {$item}";
		});
		$lastEntry = array_pop($out);
		if($lastEntry != '| ') {
			$out[] = $lastEntry;
		}
		$out = implode("\n", $out);

		$shellOut = '';
		$delim = '------------------------------------------------------';
		if($title) {
			$aux = "+-< {$title} >{$delim}";
			$shellOut .= substr($aux, 0, strlen($delim) + 1)."\n";
		} else {
			$shellOut .= "+{$delim}\n";
		}
		$shellOut .= "{$out}\n";
		$shellOut .= "+{$delim}\n";

		switch($type) {
			case \TooBasic\DebugThingTypeError:
				echo Shell\Color::Red($shellOut);
				break;
			case \TooBasic\DebugThingTypeWarning:
				echo Shell\Color::Yellow($shellOut);
				break;
			case \TooBasic\DebugThingTypeOk:
			default:
				echo $shellOut;
		}
	} else {
		$style = '';
		switch($type) {
			case \TooBasic\DebugThingTypeError:
				$style = 'border:dashed red 2px;color:red;';
				break;
			case \TooBasic\DebugThingTypeWarning:
				$style = 'border:dashed orange 2px;color:orangered;';
				break;
			case \TooBasic\DebugThingTypeOk:
			default:
				$style = 'border:dashed gray 1px;color:black;';
		}

		echo '<pre style="'.$style.'margin-left:0px;margin-right:0px;padding:5px;">';
		if($title) {
			echo ">>> {$title}\n";
		}
		echo "{$out}</pre>";
	}
}
function guessSkin() {
	global $Defaults;
	global $SkinName;

	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');

	$SkinName = isset($_REQUEST[GC_REQUEST_SKIN]) ? $_REQUEST[GC_REQUEST_SKIN] : (isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : $Defaults[GC_DEFAULTS_SKIN]);
	//
	// Skin debugs.
	if(isset($_REQUEST['debugskin'])) {
		$out = "Current Skin: '{$SkinName}'\n\n";
		$out.= 'Default Skin: '.($Defaults[GC_DEFAULTS_SKIN] ? "'{$Defaults[GC_DEFAULTS_SKIN]}'" : 'Not set')."\n";
		$out.= 'Session Skin: '.(isset($_SESSION[$sessionKey]) ? "'{$_SESSION[$sessionKey]}'" : 'Not set')."\n";
		$out.= 'URL Skin:     '.(isset($_REQUEST[GC_REQUEST_SKIN]) ? "'{$_REQUEST[GC_REQUEST_SKIN]}'" : 'Not set')."\n";

		\TooBasic\debugThing($out);
		die;
	} return $SkinName;
}
function objectCopyAndEnforce($fields, \stdClass $origin, \stdClass $destination, $defualt = array()) {
	if(!is_array($defualt)) {
		$defualt = array();
	}

	foreach($fields as $field) {
		if(isset($origin->{$field})) {
			$destination->{$field} = $origin->{$field};
		} elseif(!isset($destination->{$field})) {
			$destination->{$field} = isset($defualt[$field]) ? $defualt[$field] :
				"";
		}
	}

	return $destination;
}
function setSessionSkin($name = false) {
	global $Defaults;

	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');

	if($name) {
		$_SESSION[$sessionKey] = $name;
	} elseif(isset($_SESSION[$sessionKey])) {
		unset($_SESSION[$sessionKey]);
	}
}
