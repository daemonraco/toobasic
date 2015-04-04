<?php

namespace TooBasic;

function classname($simpleName) {
	$out = $simpleName;

	$out = str_replace(array("_", "-", ":"), " ", $out);
	$out = ucwords($out);
	$out = str_replace(" ", "", $out);

	return $out;
}
