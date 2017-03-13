<?php

/**
 * @file Exception.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Exception
 * This is the basic representation of any exception thrown by TooBasic
 * components.
 */
class Exception extends \Exception {
	//
	// Public class methods.
	public static function DisplayShellMessage(\Exception &$exception) {
		$func = function() use (&$exception) {
			echo 'Exception: '.get_class($exception)."\n";
			echo "\n";

			echo $exception->getMessage();
			echo "\n";
			echo "\n";

			$position = 0;
			foreach($exception->getTrace() as $entry) {
				echo "#{$position}  ".(isset($entry['class']) ? "{$entry['class']}::" : '')."{$entry['function']}() called at [{$entry['file']}:{$entry['line']}]\n";
				$position++;
			}
		};
		debugThing($func, DEBUG_THING_TYPE_ERROR);
		die;
	}
	public static function DisplayWebPage(\Exception &$exception) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Assignments.
		$exceptionType = get_class($exception);
		$exceptionTrace = $exception->getTrace();
		//
		// Loading and displaying exception page.
		include $Defaults[GC_DEFAULTS_EXCEPTION_PAGE];
	}
}
