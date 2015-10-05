<?php

/**
 * @file config.php
 * @author Alejandro Dario Simi
 */
//
// First thing to do is to start a PHP session.
session_start();

use \TooBasic\Sanitizer as TB_Sanitizer;
use \TooBasic\Paths as TB_Paths;

//
// Detecting current root directory.
define('ROOTDIR', dirname(__DIR__));
//
// Detecting command line access.
if(php_sapi_name() == 'cli') {
	define('__SHELL__', php_sapi_name());
}
//
// Detecting current URI.
define('ROOTURI', !defined('__SHELL__') ? dirname($_SERVER['SCRIPT_NAME']) : false);
//
// Detected mobile access.
if(!defined('__SHELL__') && isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT'])) {
	define('__IS_MOBILE__', true);
}
//
// Basic requirements.
require_once __DIR__.'/define.php';
require_once ROOTDIR.'/includes/corefunctions.php';
require_once ROOTDIR.'/includes/toobasicfunctions.php';
require_once ROOTDIR.'/includes/Sanitizer.php';
//
// Default constants configurations.
$Defaults = array();
$Defaults[GC_DEFAULTS_ACTION] = 'home';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = false;
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\File';
$Defaults[GC_DEFAULTS_CACHE_EXPIRATION] = 3600;
$Defaults[GC_DEFAULTS_CACHE_PERMISSIONS] = 0777;
$Defaults[GC_DEFAULTS_EMAIL_FROM] = 'somewhere@example.com';
$Defaults[GC_DEFAULTS_EMAIL_LAYOUT] = false;
$Defaults[GC_DEFAULTS_EMAIL_REPLAYTO] = 'noreplay@example.com';
$Defaults[GC_DEFAULTS_ERROR_PAGES] = array(
	HTTPERROR_BAD_REQUEST => HTTPERROR_BAD_REQUEST,
	HTTPERROR_FORBIDDEN => HTTPERROR_FORBIDDEN,
	HTTPERROR_INTERNAL_SERVER_ERROR => HTTPERROR_INTERNAL_SERVER_ERROR,
	HTTPERROR_NOT_FOUND => HTTPERROR_NOT_FOUND,
	HTTPERROR_NOT_IMPLEMENTED => HTTPERROR_NOT_IMPLEMENTED,
	HTTPERROR_UNAUTHORIZED => HTTPERROR_UNAUTHORIZED
);
$Defaults[GC_DEFAULTS_EXCEPTION_PAGE] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/system/others/exception_page.php');
$Defaults[GC_DEFAULTS_DEBUG_PAGE] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/system/others/debug_page.php');
$Defaults[GC_DEFAULTS_HTMLASSETS] = array(
	GC_DEFAULTS_HTMLASSETS_SCRIPTS => array(),
	GC_DEFAULTS_HTMLASSETS_STYLES => array()
);
$Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS] = array();
$Defaults[GC_DEFAULTS_INSTALLED] = false;
$Defaults[GC_DEFAULTS_LANG] = 'en_us';
$Defaults[GC_DEFAULTS_LAYOUT] = false;
$Defaults[GC_DEFAULTS_LANGS_BUILT] = false;
$Defaults[GC_DEFAULTS_LANGS_SESSIONSUFFIX] = '';
$Defaults[GC_DEFAULTS_SERVICE] = '';
$Defaults[GC_DEFAULTS_SERVICE_ALLOWEDBYSRV] = array();
$Defaults[GC_DEFAULTS_SERVICE_ALLOWEDSITES] = array();
$Defaults[GC_DEFAULTS_VIEW_ADAPTER] = '\\TooBasic\\Adapters\\View\\Smarty';
$Defaults[GC_DEFAULTS_FORMATS] = array(
	GC_VIEW_FORMAT_BASIC => $Defaults[GC_DEFAULTS_VIEW_ADAPTER],
	GC_VIEW_FORMAT_DUMP => '\\TooBasic\\Adapters\\View\\Dump',
	GC_VIEW_FORMAT_JSON => '\\TooBasic\\Adapters\\View\\JSON',
	GC_VIEW_FORMAT_PRINT => '\\TooBasic\\Adapters\\View\\Printr',
	GC_VIEW_FORMAT_SERIALIZE => '\\TooBasic\\Adapters\\View\\Serialize',
	GC_VIEW_FORMAT_XML => '\\TooBasic\\Adapters\\View\\XML'
);
$Defaults[GC_DEFAULTS_MODES] = array(
	GC_VIEW_MODE_ACTION,
	GC_VIEW_MODE_MODAL
);
$Defaults[GC_DEFAULTS_REDIRECTIONS] = array();
$Defaults[GC_DEFAULTS_MEMCACHED] = array();
$Defaults[GC_DEFAULTS_MEMCACHE] = array();
$Defaults[GC_DEFAULTS_REDIS] = array();
$Defaults[GC_DEFAULTS_SKIN] = false;
$Defaults[GC_DEFAULTS_SKIN_SESSIONSUFFIX] = '';
//
// Directory configurations.
$Directories = array();
$Directories[GC_DIRECTORIES_CACHE] = TB_Sanitizer::DirPath(ROOTDIR.'/cache');
$Directories[GC_DIRECTORIES_CONFIGS] = TB_Sanitizer::DirPath(ROOTDIR.'/config');
$Directories[GC_DIRECTORIES_INCLUDES] = TB_Sanitizer::DirPath(ROOTDIR.'/includes');
$Directories[GC_DIRECTORIES_LIBRARIES] = TB_Sanitizer::DirPath(ROOTDIR.'/libraries');
$Directories[GC_DIRECTORIES_REPRESENTATIONS] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/representations');
$Directories[GC_DIRECTORIES_MANAGERS] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/managers');
$Directories[GC_DIRECTORIES_ADAPTERS] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/adapters');
$Directories[GC_DIRECTORIES_ADAPTERS_CACHE] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/adapters/cache');
$Directories[GC_DIRECTORIES_ADAPTERS_DB] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/adapters/db');
$Directories[GC_DIRECTORIES_ADAPTERS_VIEW] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/adapters/view');
$Directories[GC_DIRECTORIES_MODULES] = TB_Sanitizer::DirPath(ROOTDIR.'/modules');
$Directories[GC_DIRECTORIES_SHELL] = TB_Sanitizer::DirPath(ROOTDIR.'/shell');
$Directories[GC_DIRECTORIES_SHELL_INCLUDES] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/shell');
$Directories[GC_DIRECTORIES_SHELL_FLAGS] = TB_Sanitizer::DirPath(ROOTDIR.'/cache/shellflags');
$Directories[GC_DIRECTORIES_SITE] = TB_Sanitizer::DirPath(ROOTDIR.'/site');
$Directories[GC_DIRECTORIES_SYSTEM] = TB_Sanitizer::DirPath(ROOTDIR.'/includes/system');
//
// Connections.
$Connections = array();
//
// Conection specs:
// $Connections[GC_CONNECTIONS_DB]['<name>'] = array(
//      GC_CONNECTIONS_DB_ENGINE   => '', // Something like: 'mysql'
//      GC_CONNECTIONS_DB_SERVER   => '', // IP or server address.
//      GC_CONNECTIONS_DB_PORT     => '', // false when defatul.
//      GC_CONNECTIONS_DB_NAME     => '', // Database name or schema.
//      GC_CONNECTIONS_DB_USERNAME => '',
//      GC_CONNECTIONS_DB_PASSWORD => '',
//      GC_CONNECTIONS_DB_PREFIX   => false  // optiona.
//      GC_CONNECTIONS_DB_SID      => false  // for Oracle only.
// );
$Connections[GC_CONNECTIONS_DB] = array();
$Connections[GC_CONNECTIONS_DEFAULTS] = array();
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB] = false;
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS] = false;
//
// Database structure configurations.
$Database = array();
$Database[GC_DATABASE_DEFAULT_SPECS] = TB_Sanitizer::DirPath(ROOTDIR.'/config/dbspecs.json');
$Database[GC_DATABASE_DB_CONNECTION_ADAPTERS] = array();
$Database[GC_DATABASE_DB_QUERY_ADAPTERS] = array(
	'mysql' => '\\TooBasic\\Adapters\\DB\\QueryMySQL',
	'sqlite' => '\\TooBasic\\Adapters\\DB\\QuerySQLite',
	'pgsql' => '\\TooBasic\\Adapters\\DB\\QueryPostgreSQL'
);
$Database[GC_DATABASE_DB_SPEC_ADAPTERS] = array(
	'mysql' => '\\TooBasic\\Adapters\\DB\\SpecMySQL',
	'sqlite' => '\\TooBasic\\Adapters\\DB\\SpecSQLite',
	'pgsql' => '\\TooBasic\\Adapters\\DB\\SpecPostgreSQL'
);
//
// Directory configurations.
$Uris = array();
$Uris[GC_URIS_INCLUDES] = TB_Sanitizer::UriPath(ROOTURI.'/includes');
//
// Paths configurations.
$Paths = array();
$Paths[GC_PATHS_CONFIGS] = '/configs';
$Paths[GC_PATHS_CONTROLLERS] = '/controllers';
$Paths[GC_PATHS_CSS] = '/styles';
$Paths[GC_PATHS_DBSPECS] = '/db';
$Paths[GC_PATHS_DBSPECSCALLBACK] = '/db';
$Paths[GC_PATHS_EMAIL_CONTROLLERS] = '/emails';
$Paths[GC_PATHS_IMAGES] = '/images';
$Paths[GC_PATHS_JS] = '/scripts';
$Paths[GC_PATHS_LANGS] = '/langs';
$Paths[GC_PATHS_LAYOUTS] = '/layouts';
$Paths[GC_PATHS_MODELS] = '/models';
$Paths[GC_PATHS_REPRESENTATIONS] = '/models/representations';
$Paths[GC_PATHS_SERVICES] = '/services';
$Paths[GC_PATHS_SHELL] = '/shell';
$Paths[GC_PATHS_SHELL_CRONS] = '/shell/crons';
$Paths[GC_PATHS_SHELL_SYSTOOLS] = '/shell/sys';
$Paths[GC_PATHS_SHELL_TOOLS] = '/shell/tools';
$Paths[GC_PATHS_SKINS] = '/skins';
$Paths[GC_PATHS_SNIPPETS] = '/snippets';
$Paths[GC_PATHS_TEMPLATES] = '/templates';
//
// SuperLoader main list.
$SuperLoader = array();
//
// Cron profiles:
// $Connections[GC_CONNECTIONS_DB]['<name>'] = array();
// $Connections[GC_CONNECTIONS_DB]['<name>'][] = array(
//        GC_CRONPROFILES_TOOL   => '',
//        GC_CRONPROFILES_PARAMS => array()
// );
$CronProfiles = array();
//
// Loading lazy requires.
require_once __DIR__.'/loader.php';
//
// Modules, site and system configuration files.
{
	$pathsProvider = TB_Paths::Instance();
	//
	// Full list of cofiguration files to load.
	$auxConfigsList = array();
	//
	// Loading specific configurations for shell or web accesses.
	if(defined('__SHELL__')) {
		//
		// Loading each extension and site sub-config file named
		// 'config_http.php'.
		$auxConfigsList = array_reverse($pathsProvider->configPath('config_shell', TB_Paths::ExtensionPHP, true));
	} else {
		//
		// Loading each extension and site sub-config file named
		// 'config_http.php'.
		$auxConfigsList = array_reverse($pathsProvider->configPath('config_http', TB_Paths::ExtensionPHP, true));
	}
	//
	// Loading each extension and site sub-config file named 'config.php'.
	$auxConfigsList = array_merge(array_reverse($pathsProvider->configPath('config', TB_Paths::ExtensionPHP, true)), $auxConfigsList);
	//
	// Requiring each extension and site sub-config file.
	foreach($auxConfigsList as $subConfig) {
		require_once $subConfig;
	}

	unset($auxConfigsList);
	unset($pathsProvider);
	unset($subConfig);
}
//
// Local configuration.
$localConfig = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
if(is_readable($localConfig)) {
	require_once $localConfig;
}
//
// Final touches.
$Defaults[GC_DEFAULTS_FORMATS]['basic'] = $Defaults[GC_DEFAULTS_VIEW_ADAPTER];

$auxParamsManager = \TooBasic\Params::Instance();
//
// Debugs lister.
if(isset($auxParamsManager->debugdebugs)) {
	$config = json_decode(file_get_contents(TB_Paths::Instance()->configPath('known_debugs', TB_Paths::ExtensionJSON)), true);
	ksort($config['debugs']);
	\TooBasic\debugThingInPage(function() use ($config) {
		echo '<ul>';
		foreach($config['debugs'] as $name => $description) {
			echo "<li><strong>{$name}</strong>: {$description}</li>";
		}
		echo '</ul>';
	}, 'Debugs');
}
//
// Routes
\TooBasic\Managers\RoutesManager::Instance()->load();
if(isset($auxParamsManager->debugroutes)) {
	//
	// This is here to avoid wrong debug prompting.
	\TooBasic\Managers\RoutesManager::Instance()->routes();
}
//
// Mandatory permissions
if(!$Defaults[GC_DEFAULTS_INSTALLED]) {
	//
	// This is here to avoid wrong debug prompting.
	\TooBasic\checkBasicPermissions();
}
//
// Necessary globals.
$ActionName = isset($auxParamsManager->{GC_REQUEST_ACTION}) ? $auxParamsManager->{GC_REQUEST_ACTION} : $Defaults[GC_DEFAULTS_ACTION];
$LayoutName = isset($auxParamsManager->{GC_REQUEST_LAYOUT}) ? $auxParamsManager->{GC_REQUEST_LAYOUT} : $Defaults[GC_DEFAULTS_LAYOUT];
$ServiceName = isset($auxParamsManager->{GC_REQUEST_SERVICE}) ? $auxParamsManager->{GC_REQUEST_SERVICE} : false;
$ModeName = isset($auxParamsManager->{GC_REQUEST_MODE}) ? $auxParamsManager->{GC_REQUEST_MODE} : false;
$SkinName = \TooBasic\guessSkin();
$LanguageName = \TooBasic\guessLanguage();

unset($auxParamsManager);
