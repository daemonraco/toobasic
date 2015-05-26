<?php

namespace TooBasic;

function classname($simpleName) {
	$out = $simpleName;

	$out = str_replace(array("_", "-", ":"), " ", $out);
	$out = ucwords($out);
	$out = str_replace(" ", "", $out);

	return $out;
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
