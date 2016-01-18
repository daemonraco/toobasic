<?php

/**
 * @file toobasicfunctions.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\Managers\ManifestsManager;

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
		$Directories[GC_DIRECTORIES_SHELL_FLAGS],
		$Directories[GC_DIRECTORIES_SYSTEM_CACHE]
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
	if(defined('__SHELL__')) {
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
function debugThingInPage($thing, $title = null) {
	//
	// Global dependencies.
	global $Defaults;
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

	$DebugPage = new \stdClass();
	$DebugPage->thing = $out;
	$DebugPage->title = $title;
	//
	// Rendering page.
	include $Defaults[GC_DEFAULTS_DEBUG_PAGE];
	//
	// When using a debug in page, no other task can be performed.
	die;
}
/**
 * This function centralize the logic to obtain the current language.
 *
 * @return string Returns a skin name.
 */
function guessLanguage() {
	//
	// Global dependencies.
	global $Defaults;
	global $LanguageName;
	//
	// Obtaing the params manager.
	$auxParamsManager = Params::Instance();
	//
	// Generating the key with which a language name is stored in the session.
	$sessionKey = GC_SESSION_LANGUAGE.($Defaults[GC_DEFAULTS_LANGS_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_LANGS_SESSIONSUFFIX]}" : '');
	//
	// Obtaining current language from:
	//	- 1st: url parameter.
	//	- 2nd: session variables.
	//	- 3rd: defualts.
	$LanguageName = isset($auxParamsManager->{GC_REQUEST_LANGUAGE}) ? $auxParamsManager->{GC_REQUEST_LANGUAGE} : (isset($_SESSION[$sessionKey]) ? $_SESSION[$sessionKey] : $Defaults[GC_DEFAULTS_LANG]);
	//
	// Skin debugs.
	if(isset($auxParamsManager->debuglang)) {
		\TooBasic\debugThing(function() use ($sessionKey, $auxParamsManager) {
			//
			// Global dependencies.
			global $Defaults;
			global $LanguageName;

			echo "Current Language: '{$LanguageName}'\n\n";
			echo 'Default Language: '.($Defaults[GC_DEFAULTS_LANG] ? "'{$Defaults[GC_DEFAULTS_LANG]}'" : 'Not set')."\n";
			echo 'Session Language: '.(isset($_SESSION[$sessionKey]) ? "'{$_SESSION[$sessionKey]}'" : 'Not set')."\n";
			echo 'URL Language:     '.(isset($auxParamsManager->{GC_REQUEST_LANGUAGE}) ? "'".$auxParamsManager->{GC_REQUEST_LANGUAGE}."'" : 'Not set')."\n";
		});
		//
		// Triggering the next debug message.
		\TooBasic\Translate::Instance()->get('');
	}
	//
	// Returtning found skin name.
	return $LanguageName;
}
/**
 * This function centralize the logic to obtain the current skin.
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
 * @private
 * This is an internal method that belongs to 'getConfigurationFilesList()' and it
 * builds a recursive list of files based on a list of dependencies where keys in
 * the list are paths with dependencies and values are list of dependencies paths.
 *
 * @param string[string] $fullList List of dependencies.
 * @param string $start First path to analize.
 * @return string[] Flat list of dependencies.
 */
function _configurationTreeSolver($fullList, $start) {
	$out = array();

	foreach($fullList[$start] as $subDependency) {
		$out[] = $subDependency;
		$out = array_merge($out, _configurationTreeSolver($fullList, $subDependency));
	}

	return $out;
}
/**
 * This function generates a proper list of configuration files considering
 * dependencies between modules and also stores such priority calculation into a
 * cached file for better performance.
 */
function getConfigurationFilesList() {
	//
	// Global dependencies.
	global $Directories;
	global $Paths;
	//
	// Paths helpers.
	$pathsProvider = Paths::Instance();
	//
	// Full list of cofiguration files to load.
	$out = array();
	//
	// Loading specific configurations for shell or web accesses.
	if(defined('__SHELL__')) {
		//
		// Loading each extension and site sub-config file named
		// 'config_http.php'.
		$out = $pathsProvider->configPath('config_shell', Paths::ExtensionPHP, true);
	} else {
		//
		// Loading each extension and site sub-config file named
		// 'config_http.php'.
		$out = $pathsProvider->configPath('config_http', Paths::ExtensionPHP, true);
	}
	//
	// Loading each extension and site sub-config file named 'config.php'.
	$out = array_merge($pathsProvider->configPath('config', Paths::ExtensionPHP, true), $out);
	//
	// Priorities @{
	//
	// Loading and checking cached configuration priorities.
	$priotitiesOk = true;
	$prioritiesData = false;
	$prioritiesPath = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SYSTEM_CACHE]}/config-priorities.json");
	$whatList = defined('__SHELL__') ? 'shell' : 'http';
	//
	// Checking existence.
	if(is_file($prioritiesPath)) {
		//
		// Lading cached data.
		$prioritiesData = json_decode(file_get_contents($prioritiesPath));
		//
		// Checking that it's considering all config files.
		if(!isset($prioritiesData->{$whatList})) {
			$priotitiesOk = false;
		} elseif(count($out) != count($prioritiesData->{$whatList}->configsList)) {
			$priotitiesOk = false;
		}
	} else {
		$priotitiesOk = false;
	}
	//
	// If there's no valid cache file, it should be generated.
	if(!is_file($prioritiesPath) || !$priotitiesOk) {
		//
		// Basic structure.
		if($prioritiesData === false) {
			$prioritiesData = new \stdClass();
		}
		$prioritiesData->{$whatList} = new \stdClass();
		$prioritiesData->{$whatList}->preConfigsList = $out;
		//
		// Paths helpers.
		$manifestsProvider = ManifestsManager::Instance();
		//
		// Top config prefixes.
		$topPrefixes = array(
			$Directories[GC_DIRECTORIES_SYSTEM],
			$Directories[GC_DIRECTORIES_SITE]
		);
		//
		// Loading all manifests.
		$manifests = $manifestsProvider->manifests();
		//
		// Building list of module path dependencies and a list of paths
		// associated with their priorities.
		$requirementLinks = array();
		$modulePriorities = array();
		foreach($manifests as $manifest) {
			//
			// Current module path.
			$path = $manifest->modulePath();
			//
			// Default values.
			$requirementLinks[$path] = array();
			$modulePriorities[$path] = 0;
			//
			// Checking the existence of requirements.
			$requirements = $manifest->requiredModules();
			if($requirements) {
				//
				// Associating requirements.
				foreach($requirements as $subManifest) {
					$aux = $subManifest->modulePath();
					$requirementLinks[$path][] = $aux;
				}
			}
		}
		//
		// Assigning priorities depending on how required a path is. This
		// considers dependencies of dependencies.
		foreach($requirementLinks as $path => $dependencies) {
			//
			// Recursive list of dependencies.
			$fullDependencies = _configurationTreeSolver($requirementLinks, $path);
			//
			// Increasing priority of each depdendency.
			foreach($fullDependencies as $subDependency) {
				$modulePriorities[$subDependency] ++;
			}
		}
		//
		// Sorting priorities.
		arsort($modulePriorities);
		//
		// Separating interesting paths into:
		//	- list of non-module paths on the top.
		//	- list of module paths:
		//		- paths with dependencies.
		//		- paths without dependencies.
		//	- list of non-module paths on the bottom.
		// @{
		//
		// Default values.
		$pathsByPriority = array(
			GC_AFIELD_TOP => array(),
			GC_AFIELD_MIDDLE => array(),
			GC_AFIELD_BOTTOM => array()
		);
		//
		// Basic list order.
		sort($out);
		//
		// Separating top paths.
		foreach($topPrefixes as $tPrefix) {
			foreach($out as $k => $path) {
				//
				// Guessing path prefixes.
				$prefix = dirname(dirname($path));
				if($prefix == $tPrefix) {
					$pathsByPriority[GC_AFIELD_TOP][] = $path;
					unset($out[$k]);
				}
			}
		}
		//
		// Separating module paths considering their priorities.
		foreach(array_keys($modulePriorities) as $tPrefix) {
			foreach($out as $k => $path) {
				//
				// Guessing path prefixes.
				$prefix = dirname(dirname($path));
				if($prefix == $tPrefix) {
					$pathsByPriority[GC_AFIELD_MIDDLE][] = $path;
					unset($out[$k]);
				}
			}
		}
		//
		// The rest are bottom paths.
		$pathsByPriority[GC_AFIELD_BOTTOM] = $out;
		// @}
		//
		// Rebuiding final result.
		$out = array_merge($pathsByPriority[GC_AFIELD_TOP], $pathsByPriority[GC_AFIELD_MIDDLE], $pathsByPriority[GC_AFIELD_BOTTOM]);
		//
		// Setting new information.
		$prioritiesData->{$whatList}->configsList = $out;
		$prioritiesData->{$whatList}->modulePriorities = $modulePriorities;
		$prioritiesData->{$whatList}->requirementLinks = $requirementLinks;
		$prioritiesData->{$whatList}->pathsByPriority = $pathsByPriority;
		//
		// Saving information.
		file_put_contents($prioritiesPath, json_encode($prioritiesData, JSON_PRETTY_PRINT));
		@chmod($prioritiesPath, 0666);
	} else {
		//
		// Using the previously calculated list.
		$out = $prioritiesData->{$whatList}->configsList;
	}
	// @}
	//
	// Debugging loading mechanism.
	if(isset(Params::Instance()->debugconfigs)) {
		\TooBasic\debugThingInPage(function() use ($prioritiesData) {
			foreach(array('http', 'shell') as $whatList) {
				if(!isset($prioritiesData->{$whatList})) {
					continue;
				}

				echo '<div class="panel panel-default">';
				echo '<div class="panel-heading">Behavior for: '.strtoupper($whatList).'</div>';
				echo '<div class="panel-body">';

				echo '<h4>Loading Order:</h4>';
				echo '<ul>';
				foreach($prioritiesData->{$whatList}->configsList as $path) {
					echo "<li>{$path}</li>";
				}
				echo '</ul>';

				echo '<h4>Module priorities:</h4>';
				echo '<dl class="dl-horizontal">';
				foreach($prioritiesData->{$whatList}->modulePriorities as $path => $priority) {
					$basename = basename($path);
					echo "<dt>{$basename}:<dt><dd><kbd>{$priority}</kbd></dd>";
				}
				echo '</dl>';

				echo '<h4>Requirement Links:</h4>';
				echo '<ul>';
				foreach($prioritiesData->{$whatList}->requirementLinks as $path => $dependencies) {
					if($dependencies) {
						$basename = basename($path);
						echo "<li>{$basename}:<ul>";
						foreach($dependencies as $dependency) {
							$dBasename = basename($dependency);
							echo "<li>{$dBasename}</li>";
						}
						echo '</ul></li>';
					}
				}
				echo '</ul>';
				echo '</div></div>';
			}
		}, 'Debugs');
	}

	return $out;
}
/**
 * This method copies an object's fields into another object and enforces the
 * existence of a list of field.
 *
 * @param string[] $fields List of field names that must be present at the end of
 * the copy. 
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
 * This function allows to properly set a current language name into session.
 *
 * @param string $name Language name to set.
 */
function setSessionLanguage($name = false) {
	//
	// Global dependencies.
	global $Defaults;
	//
	// Generating the key with which a language name is stored in the session.
	$sessionKey = GC_SESSION_SKIN.($Defaults[GC_DEFAULTS_LANGS_SESSIONSUFFIX] ? "-{$Defaults[GC_DEFAULTS_LANGS_SESSIONSUFFIX]}" : '');
	//
	// If a name was given, it is set. Otherwise, the previous setting is
	// removed.
	if($name) {
		$_SESSION[$sessionKey] = "{$name}";
	} elseif(isset($_SESSION[$sessionKey])) {
		unset($_SESSION[$sessionKey]);
	}
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
