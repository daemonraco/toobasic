<?php

/**
 * @file toobasicfunctions.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Global constants for the generic debug message printer @{
const DebugThingTypeOk = 'ok';
const DebugThingTypeError = 'error';
const DebugThingTypeWarning = 'warning';
// @}
/**
 * This basic function checks for writing permissions on core directories, if any
 * of them is wrong, TooBasic aborts its execution and prompts an error.
 */
function checkBasicPermissions() {
	//
	// Global dependencies.
	global $Directories;
	//
	// List of directories that required writting permissions.
	$writableDirectories = array(
		$Directories[GC_DIRECTORIES_CACHE],
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/filecache"),
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/langs"),
		Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/shellflags")
	);
	//
	// Checking each directory.
	foreach($writableDirectories as $path) {
		//
		// Checking if it really is a directory.
		if(!is_dir($path)) {
			debugThing("'{$path}' is not a directory", \TooBasic\DebugThingTypeError);
			die;
		}
		//
		// Checking if the current system user has permissions to write
		// inside it.
		if(!is_writable($path)) {
			debugThing("'{$path}' is not writable", \TooBasic\DebugThingTypeError);
			die;
		}
	}
}
/**
 * @deprecated
 * This function normalize class names:
 *
 * @param string $simpleName Name to be normalized.
 * @return string Returns a normalized name.
 */
function classname($simpleName) {
	//
	// Default values.
	$out = $simpleName;
	//
	// Cleaning special charaters
	$out = str_replace(array('_', '-', ':'), ' ', $out);
	$out = ucwords($out);
	$out = str_replace(' ', '', $out);
	//
	// Returning a clean name.
	return $out;
}
/**
 * This method prints in a basic but standard way some message.
 *
 * @param mixed $thing Thing to be shown.
 * @param string $type The way it should be shown.
 * @param string $title If present, the shown message will present this parameter
 * as a title.
 */
function debugThing($thing, $type = \TooBasic\DebugThingTypeOk, $title = null) {
	//
	// Storing data displayed in a buffer for post processing.
	ob_start();
	//
	// Trying to print it in the best way.
	if(is_bool($thing)) {
		//
		// When it's a boolean, true or false should be printed.
		echo (boolval($thing) ? 'true' : 'false')."\n";
	} elseif(is_null($thing)) {
		//
		// When its null, it 'NULL'.
		echo "NULL\n";
	} elseif(is_callable($thing) || $thing instanceof \Closure) {
		//
		// If it's the name of a callable function, it should be executed.
		$thing();
	} elseif(is_object($thing) || is_array($thing)) {
		//
		// Objects and arrays go through 'print_r()'.
		print_r($thing);
	} else {
		//
		// Otherwise, they go directly.
		echo "{$thing}\n";
	}
	//
	// Obtaining buffer's data and closing it.
	$out = ob_get_contents();
	ob_end_clean();
	//
	// Shell and non shell should look different.
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
/**
 * This function centralize the logic to obtain the current set skin.
 *
 * @return string Returns a skin name.
 */
function guessSkin() {
	//
	// Global dependencies.
	global $Defaults;
	global $SkinName;
	//
	// Obtaing the params manager.
	$auxParamsManager = Params::Instance();
	//
	// Generating the key with which a skin name is stored in the session.
	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');
	//
	// Obtaining current skin from:
	//	- 1st: url parameter.
	//	- 2nd: session variables.
	//	- 3rd: defualts.
	$SkinName = isset($auxParamsManager->{GC_REQUEST_SKIN}) ? $auxParamsManager->{GC_REQUEST_SKIN} : (isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : $Defaults[GC_DEFAULTS_SKIN]);
	//
	// Skin debugs.
	if(isset($auxParamsManager->debugskin)) {
		$out = "Current Skin: '{$SkinName}'\n\n";
		$out.= 'Default Skin: '.($Defaults[GC_DEFAULTS_SKIN] ? "'{$Defaults[GC_DEFAULTS_SKIN]}'" : 'Not set')."\n";
		$out.= 'Session Skin: '.(isset($_SESSION[$sessionKey]) ? "'{$_SESSION[$sessionKey]}'" : 'Not set')."\n";
		$out.= 'URL Skin:     '.(isset($auxParamsManager->{GC_REQUEST_SKIN}) ? "'".$auxParamsManager->{GC_REQUEST_SKIN}."'" : 'Not set')."\n";

		\TooBasic\debugThing($out);
		die;
	}
	//
	// Returtning found skin name.
	return $SkinName;
}
/**
 * This method copies an object's fields into another object and enforces the
 * existence of a list of field.
 *
 * @param string[] $fields List of field names that must be present at the end of the
 * copy. 
 * @param \stdClass $origin Object from which take vales.
 * @param \stdClass $destination Object in which values has to be copied.
 * @param mixed[string] $defualt Associative list of values to be used as default
 * on enforced fields. If one of them is not present and empty string is used as
 * default.
 * @return \stdClass Returns the destination object with it's values overriden by
 * those in the origin object and, if it was necessary, with enforced fields.
 */
function objectCopyAndEnforce($fields, \stdClass $origin, \stdClass $destination, $defualt = array()) {
	//
	// If the list of defaults is not an array, it's forced to be an empty
	// array.
	if(!is_array($defualt)) {
		$defualt = array();
	}
	//
	// Checking each required field.
	foreach($fields as $field) {
		//
		// If the field is prensent in the origin, it must override the
		// destination field.
		// If not and the destination object doesn't have it either, it
		// must be enforced using default values.
		if(isset($origin->{$field})) {
			$destination->{$field} = $origin->{$field};
		} elseif(!isset($destination->{$field})) {
			$destination->{$field} = isset($defualt[$field]) ? $defualt[$field] : '';
		}
	}
	//
	// Returning the destination object with fields copied and enforced.
	return $destination;
}
/**
 * This function allows to properly set a current skin name into session.
 *
 * @param string $name Skin name to set.
 */
function setSessionSkin($name = false) {
	//
	// Global dependencies.
	global $Defaults;
	//
	// Generating the key with which a skin name is stored in the session.
	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX]}" : '');
	//
	// If a name was given, it is set. Otherwise, the previous setting is
	// removed.
	if($name) {
		$_SESSION[$sessionKey] = "{$name}";
	} elseif(isset($_SESSION[$sessionKey])) {
		unset($_SESSION[$sessionKey]);
	}
}
