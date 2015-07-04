<?php

$Defaults[GC_REQUEST_ACTION] = 'starterdoc';
$Defaults[GC_REQUEST_SKIN] = 'united';

$Defaults['starterdoc-allow-skins'] = true;
/**
 * This function restores default settings given by this module.
 */
function starterdoc_disable() {
	global $Defaults;

	$Defaults[GC_REQUEST_ACTION] = 'home';
	$Defaults[GC_REQUEST_SKIN] = false;
	$Defaults['starterdoc-allow-skins'] = false;
}
