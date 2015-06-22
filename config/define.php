<?php

//
// TooBasic generic constants @{
define('TOOBASIC_VERSION', '0.3.0');
// @}
//
// HTTP errors @{
define('HTTPERROR_OK', '200');
define('HTTPERROR_BAD_REQUEST', '400');
define('HTTPERROR_UNAUTHORIZED', '401');
define('HTTPERROR_FORBIDDEN', '403');
define('HTTPERROR_NOT_FOUND', '404');
define('HTTPERROR_INTERNAL_SERVER_ERROR', '500');
define('HTTPERROR_NOT_IMPLEMENTED', '501');
// @}
//
// Global's constants @{
//
// Directories @{
define('GC_DEFAULTS_ACTION', 'action');
define('GC_DEFAULTS_ALLOW_ROUTES', 'allow-routes');
define('GC_DEFAULTS_CACHE_ADAPTER', 'cache-adapter');
define('GC_DEFAULTS_CACHE_EXPIRATION', 'cache-expiration');
define('GC_DEFAULTS_CACHE_PERMISSIONS', 'cache-permissions');
define('GC_DEFAULTS_INSTALLED', 'installed');
define('GC_DEFAULTS_LANGS_DEFAULTLANG', 'langs-defaultlang');
define('GC_DEFAULTS_LAYOUT', 'layout');
define('GC_DEFAULTS_LANGS_BUILT', 'langs-built');
define('GC_DEFAULTS_SERVICE', 'service');
define('GC_DEFAULTS_VIEW_ADAPTER', 'view-adapter');
define('GC_DEFAULTS_FORMATS', 'formats');
define('GC_DEFAULTS_MODES', 'modes');
define('GC_DEFAULTS_SKIN', 'skin');
define('GC_DEFAULTS_SKIN_SESSIONSUFFIX', 'skin-suffix');
// @}
//
// Directories @{
define('GC_DIRECTORIES_CACHE', 'cache');
define('GC_DIRECTORIES_CONFIGS', 'configs');
define('GC_DIRECTORIES_INCLUDES', 'includes');
define('GC_DIRECTORIES_LIBRARIES', 'libraries');
define('GC_DIRECTORIES_REPRESENTATIONS', 'representations');
define('GC_DIRECTORIES_MANAGERS', 'managers');
define('GC_DIRECTORIES_ADAPTERS', 'adapters');
define('GC_DIRECTORIES_ADAPTERS_CACHE', 'adapters-cache');
define('GC_DIRECTORIES_ADAPTERS_DB', 'adapters-db');
define('GC_DIRECTORIES_ADAPTERS_VIEW', 'adapters-view');
define('GC_DIRECTORIES_MODULES', 'modules');
define('GC_DIRECTORIES_SHELL', 'shell');
define('GC_DIRECTORIES_SHELL_INCLUDES', 'shell-includes');
define('GC_DIRECTORIES_SHELL_FLAGS', 'shell-flags');
define('GC_DIRECTORIES_SITE', 'site');
define('GC_DIRECTORIES_SYSTEM', 'system');
// @}
//
// Connections @{
define('GC_CONNECTIONS_DB', 'db');
define('GC_CONNECTIONS_DB_ENGINE', 'engine');
define('GC_CONNECTIONS_DB_SERVER', 'server');
define('GC_CONNECTIONS_DB_PORT', 'port');
define('GC_CONNECTIONS_DB_NAME', 'name');
define('GC_CONNECTIONS_DB_USERNAME', 'username');
define('GC_CONNECTIONS_DB_PASSWORD', 'password');
define('GC_CONNECTIONS_DB_PREFIX', 'prefix');
define('GC_CONNECTIONS_DB_SID', 'sid');
define('GC_CONNECTIONS_DEFAUTLS', 'defaults');
define('GC_CONNECTIONS_DEFAUTLS_DB', 'db');
define('GC_CONNECTIONS_DEFAUTLS_KEEPUNKNOWNS', 'keep-unknowns');
define('GC_CONNECTIONS_DEFAUTLS_INSTALL', 'dbinstall');
define('GC_CONNECTIONS_DEFAUTLS_CACHE', 'cache');
// @}
//
// Database structure @{
define('GC_DATABASE_DEFAULT_SPECS', 'default-specs');
define('GC_DATABASE_DB_ADAPTERS', 'db-adapters');
// @}
//
// URIs @{
define('GC_URIS_INCLUDES', 'includes');
// @}
//
// Paths @{
define('GC_PATHS_CONFIGS', 'configs');
define('GC_PATHS_CONTROLLERS', 'controllers');
define('GC_PATHS_SERVICES', 'services');
define('GC_PATHS_CSS', 'css');
define('GC_PATHS_DBSPECS', 'dbspecs');
define('GC_PATHS_DBSPECSCALLBACK', 'dbspecscallbacks');
define('GC_PATHS_IMAGES', 'images');
define('GC_PATHS_JS', 'js');
define('GC_PATHS_LANGS', 'langs');
define('GC_PATHS_LAYOUTS', 'layouts');
define('GC_PATHS_MODELS', 'models');
define('GC_PATHS_REPRESENTATIONS', 'representations');
define('GC_PATHS_SHELL', 'shell');
define('GC_PATHS_SHELL_CRONS', 'shell-crons');
define('GC_PATHS_SHELL_SYSTOOLS', 'shell-sys');
define('GC_PATHS_SHELL_TOOLS', 'shell-tools');
define('GC_PATHS_SKINS', 'skins');
define('GC_PATHS_SNIPPETS', 'snippets');
define('GC_PATHS_TEMPLATES', 'templates');
// @}
//
// Cron profile's parameters @{
define('GC_CRONPROFILES_TOOL', 'tool');
define('GC_CRONPROFILES_PARAMS', 'params');
// @}
//
// Session parameters @{
define('GC_SESSION_SKIN', 'toobasic-skin');
// @}
//
// Request parameters @{
define('GC_REQUEST_ACTION', 'action');
define('GC_REQUEST_LAYOUT', 'layout');
define('GC_REQUEST_MODE', 'mode');
define('GC_REQUEST_SERVICE', 'service');
define('GC_REQUEST_SKIN', 'skin');
// @}
//
// Class Suffixes @{
define('GC_CLASS_SUFFIX_CONTROLLER', 'Controller');
define('GC_CLASS_SUFFIX_CRON', 'Cron');
define('GC_CLASS_SUFFIX_FACTORY', 'Factory');
define('GC_CLASS_SUFFIX_MODEL', 'Model');
define('GC_CLASS_SUFFIX_REPRESENTATION', 'Representation');
define('GC_CLASS_SUFFIX_SERVICE', 'Service');
define('GC_CLASS_SUFFIX_TOOL', 'Tool');
define('GC_CLASS_SUFFIX_SYSTOOL', 'Systool');
// @}
//
// View formats @{
define('GC_VIEW_FORMAT_BASIC', 'basic');
define('GC_VIEW_FORMAT_DUMP', 'dump');
define('GC_VIEW_FORMAT_JSON', 'json');
define('GC_VIEW_FORMAT_PRINT', 'print');
define('GC_VIEW_FORMAT_SERIALIZE', 'serialize');
define('GC_VIEW_FORMAT_XML', 'xml');
// @}
//
// View modes @{
define('GC_VIEW_MODE_ACTION', 'action');
define('GC_VIEW_MODE_MODAL', 'modal');
// @}
//
// Memcached parameters @{
define('GC_DEFAULTS_MEMCACHED', 'memcached');
define('GC_DEFAULTS_MEMCACHED_SERVER', 'server');
define('GC_DEFAULTS_MEMCACHED_PORT', 'port');
define('GC_DEFAULTS_MEMCACHED_PREFIX', 'prefix');
// @}
// Memcached parameters @{
define('GC_DEFAULTS_MEMCACHE', 'memcache');
define('GC_DEFAULTS_MEMCACHE_SERVER', 'server');
define('GC_DEFAULTS_MEMCACHE_PORT', 'port');
define('GC_DEFAULTS_MEMCACHE_PREFIX', 'prefix');
define('GC_DEFAULTS_MEMCACHE_COMPRESSED', 'compressed');
// @}
// @}
