<?php

if(!function_exists("get_called_class")) {
	function get_called_class() {
		$bt = debug_backtrace();
		$l = 0;
		$matches = false;
		do {
			$l++;
			$lines = file($bt[$l]["file"]);
			$callerLine = $lines[$bt[$l]["line"] - 1];
			preg_match("/([a-zA-Z0-9\_]+)::".$bt[$l]["function"]."/", $callerLine, $matches);
		} while($matches[1] === "parent" && $matches[1]);

		return $matches[1];
	}
}
function debugit($data, $final = false, $specific = false, $name = null, $showTrace = false) {
	ob_start();

	if($specific) {
		var_dump($data);
	} else {
		if(is_bool($data)) {
			echo (boolval($data) ? "true" : "false")."\n";
		} elseif(is_null($data)) {
			echo "NULL\n";
		} elseif(is_object($data) || is_array($data)) {
			print_r($data);
		} else {
			echo "{$data}\n";
		}
	}

	$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

	$callingLine = array_shift($trace);
	$callerLine = array_shift($trace);

	if($showTrace) {
		echo "\n";
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}

	echo "\n";
	echo "At: ".(isset($callerLine["class"]) ? "{$callerLine["class"]}::" : "")."{$callerLine["function"]}() [{$callingLine["file"]}:{$callingLine["line"]}]\n";

	$out = ob_get_contents();
	ob_end_clean();

	if(defined("__SHELL__")) {
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

	if($final) {
		die;
	}
}
