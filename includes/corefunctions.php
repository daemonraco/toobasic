<?php

/**
 * @file corefunctions.php
 * @author Alejandro Dario Simi
 */
//
// Defining function 'getallheaders()' in case the current PHP version doesn't
// have it.
if(!function_exists('getallheaders')) {
	function getallheaders() {
		return apache_request_headers();
	}
}
//
// Defining function 'apache_request_headers()' in case the current PHP version
// doesn't have it.
if(!function_exists('apache_request_headers')) {
	function apache_request_headers() {
		$arh = [];
		$rx_http = '/\AHTTP_/';
		foreach($_SERVER as $key => $val) {
			if(preg_match($rx_http, $key)) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = [];
				// do some nasty string manipulations to restore
				// the original letter case this should work in
				// most cases.
				$rx_matches = explode('_', strtolower($arh_key));
				if(count($rx_matches) > 0 && strlen($arh_key) > 2) {
					foreach($rx_matches as $ak_key => $ak_val) {
						$rx_matches[$ak_key] = ucfirst($ak_val);
					}
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		if(isset($_SERVER['CONTENT_TYPE'])) {
			$arh['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		}
		if(isset($_SERVER['CONTENT_LENGTH'])) {
			$arh['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		}
		return($arh);
	}
}
/**
 * Prompt an object in a pretty way and adding some useful info like where it is
 * being called from.
 * 
 * @param mixed $data Object to be prompt.
 * @param boolean $final When true, abort the execution after prompting.
 * @param boolean $specific Uses 'var_dump()' instead of 'print_r()'.
 * @param string $name Adds a title to the seccion where the object is prompted.
 * @param boolean $showTrace Adds callback trace.
 */
function debugit($data, $final = false, $specific = false, $name = null, $showTrace = false) {
	//
	// Storing data displayed in a buffer for post processing.
	ob_start();
	//
	// When it is specific, it shoud use 'var_dump()'.
	if($specific) {
		var_dump($data);
	} else {
		$type = gettype($data);
		switch($type) {
			case 'boolean':
				//
				// When it's boolean, should say true or false.
				echo (boolval($data) ? 'true' : 'false')."\n";
				break;
			case 'NULL':
				//
				// When it's null, should NULL.
				echo "NULL\n";
				break;
			case 'array':
				//
				// When it's an object, should use 'print_r()'.
				print_r($data);
				break;
			case 'object':
				$class = get_class($data);
				//
				// Depending on the type it may be shown
				// differently.
				switch($class) {
					case 'PDOStatement':
						echo "Query: {$data->queryString}\n";

						$info = $data->errorInfo();
						echo "Error Information:\n";
						echo "\tError code:        {$info[0]}\n";
						echo "\tDriver error code: {$info[1]}\n";
						echo "\tError message:     {$info[2]}\n";
						break;
					default:
						//
						// When it's a simple object, should use 'print_r()'.
						print_r($data);
				}
				break;
			default:
				//
				// Otherwise, it goes directly.
				echo "{$data}\n";
		}
	}
	//
	// Obtaining caller information.
	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	$callingLine = array_shift($trace);
	$callerLine = array_shift($trace);
	//
	// When it's requested, a back trace should be promptted.
	if($showTrace) {
		echo "\n";
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}
	//
	// Printing information about the location where this function was called.
	echo "\n";
	echo 'At: '.(isset($callerLine['class']) ? "{$callerLine['class']}::" : '')."{$callerLine['function']}() [{$callingLine['file']}:{$callingLine['line']}]\n";
	//
	// Obtaining information from the buffer and closing it.
	$out = ob_get_contents();
	ob_end_clean();
	//
	// Shell and non-shell debug seccion have different looks.
	if(defined('__SHELL__')) {
		$out = explode("\n", $out);
		array_walk($out, function(&$item) {
			$item = "| {$item}";
		});
		$out = implode("\n", $out);

		$delim = "------------------------------------------------------";
		if($name) {
			$aux = "+-< {$name} >{$delim}";
			echo substr($aux, 0, strlen($delim) + 1)."\n";
		} else {
			echo "+{$delim}\n";
		}
		echo "{$out}\n";
		echo "+{$delim}\n";
	} else {
		echo '<pre style="border:dashed gray 1px;width:100%;padding:5px;">';
		if($name) {
			echo ">>> {$name}\n";
		}
		echo "{$out}</pre>";
	}
	//
	// If it's final, it show abort after showing the debug information.
	if($final) {
		die;
	}
}
/**
 * This tool allows to remove a directory along with its contents. If a file path
 * is given instead of a directory, it will be remove without problem.
 *
 * @param string $path Directory path to remove.
 * @param string $keepFather Whether to keep the directory entry and only remove
 * its contents, or remove it all.
 * @return string[] Returns a list of errors found while removing the directory.
 */
function recursive_unlink($path, $keepFather = false) {
	//
	// Creating a list of errors to be returned.
	$out = [];
	//
	// Checking if the requested path exists.
	if(is_readable($path)) {
		//
		// When it's a directory all it's contents have to be removed
		// first.
		// Otherwise it's simply removed.
		if(is_dir($path)) {
			//
			// Attempting to open the directory.
			if($dir = @opendir($path)) {
				//
				// Walking over each directory entry.
				while($entry = readdir($dir)) {
					//
					// Ignoring pseudo-directories.
					if($entry != '..' && $entry != '.') {
						//
						// Recursively removing elements.
						foreach(recursive_unlink($path.DIRECTORY_SEPARATOR.$entry, false) as $v) {
							//
							// Adding all errors.
							array_push($out, $v);
						}
					}
				}
				//
				// Closing directory descriptor.
				closedir($dir);
				//
				// Checking if current path has to be remove along
				// with its contents or only its contents.
				if(!$keepFather) {
					//
					// Removing directory path.
					if(!@rmdir($path)) {
						//
						// At this point, an error has to be registered.
						array_push($out, error_get_last());
					}
				}
			} else {
				//
				// At this point, an error has to be registered.
				array_push($out, error_get_last());
			}
		} else {
			//
			// Attempting to remove path.
			if(!@unlink($path)) {
				//
				// At this point, an error has to be registered.
				array_push($out, error_get_last());
			}
		}
	} else {
		//
		// Registering an error.
		array_push($out, "{$path}: No such a file o directory");
	}
	//
	// Returning the list of errors.
	return $out;
}
