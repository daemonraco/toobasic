<?php

/**
 * @file config.php
 * @author Alejandro Dario Simi
 */
//
// Backing up default configurations found at this point for further restore @{
$StarterdocDefaultsBackUps = array();
$StarterdocDefaultsBackUps[GC_DEFAULTS_ACTION] = $Defaults[GC_DEFAULTS_ACTION];
$StarterdocDefaultsBackUps[GC_DEFAULTS_ERROR_PAGES] = $Defaults[GC_DEFAULTS_ERROR_PAGES];
$StarterdocDefaultsBackUps[GC_DEFAULTS_SKIN] = $Defaults[GC_DEFAULTS_SKIN];
// @}
//
// Setting this modules as the main application @{
$Defaults[GC_DEFAULTS_ACTION] = 'sdoc_home';
$Defaults[GC_DEFAULTS_SKIN] = 'simplex';
$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_BAD_REQUEST] = 'starterdoc_'.HTTPERROR_BAD_REQUEST;
$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_FORBIDDEN] = 'starterdoc_'.HTTPERROR_FORBIDDEN;
$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_INTERNAL_SERVER_ERROR] = 'starterdoc_'.HTTPERROR_INTERNAL_SERVER_ERROR;
$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_NOT_FOUND] = 'starterdoc_'.HTTPERROR_NOT_FOUND;
$Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS]['starterdoc_top'] = array(
	GC_DEFAULTS_HTMLASSETS_SCRIPTS => array(
		'lib:jquery/jquery-2.1.3.min.js',
		'jquery.cssemoticons.min'
	),
	GC_DEFAULTS_HTMLASSETS_STYLES => array(
		'starterdoc',
		'jquery.cssemoticons'
	)
);
$Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS]['starterdoc_bottom'] = array(
	GC_DEFAULTS_HTMLASSETS_SCRIPTS => array(
		'github_names',
		'starterdoc_mdfix'
	)
);
$Defaults['starterdoc-allow-skins'] = true;
// @}
/**
 * This function restores default settings and remove those given by this module.
 */
function starterdoc_disable() {
	global $Defaults;
	global $StarterdocDefaultsBackUps;

	$Defaults[GC_DEFAULTS_ACTION] = $StarterdocDefaultsBackUps[GC_DEFAULTS_ACTION];
	$Defaults[GC_DEFAULTS_SKIN] = $StarterdocDefaultsBackUps[GC_DEFAULTS_SKIN];
	$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_BAD_REQUEST] = $StarterdocDefaultsBackUps[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_BAD_REQUEST];
	$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_FORBIDDEN] = $StarterdocDefaultsBackUps[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_FORBIDDEN];
	$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_INTERNAL_SERVER_ERROR] = $StarterdocDefaultsBackUps[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_INTERNAL_SERVER_ERROR];
	$Defaults[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_NOT_FOUND] = $StarterdocDefaultsBackUps[GC_DEFAULTS_ERROR_PAGES][HTTPERROR_NOT_FOUND];
	$Defaults['starterdoc-allow-skins'] = false;
}
