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
function debugit($data, $final = false, $specific = false, $name = null) {
	echo '<pre style="border:dashed gray 1px;width:100%;padding:5px;">';

	if($name) {
		echo ">>> {$name}\n";
	}

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
//print_r($trace);
//die;

	$caller = array(
		"file" => __FILE__
	);
	while($caller["file"] == __FILE__ && $trace) {
		$caller = array_shift($trace);
	}
	echo "\n";
	echo "At: ".(isset($caller["class"]) ? "{$caller["class"]}::" : "")."{$caller["function"]}() [{$caller["file"]}:{$caller["line"]}]\n";

	echo "</pre>\n";
	if($final) {
		die;
	}
}
