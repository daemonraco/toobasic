<?php

/**
 * @file define.php
 * @author Alejandro Dario Simi
 */
//
// TooBasic generic constants @{
define('TOOBASIC_VERSION', '2.2.0');
define('TOOBASIC_VERSION_NAME', 'mamba');
// @}
//
// HTTP errors @{
define('HTTPERROR_OK', '200');
define('HTTPERROR_BAD_REQUEST', '400');
define('HTTPERROR_FORBIDDEN', '403');
define('HTTPERROR_INTERNAL_SERVER_ERROR', '500');
define('HTTPERROR_NOT_FOUND', '404');
define('HTTPERROR_NOT_IMPLEMENTED', '501');
define('HTTPERROR_UNAUTHORIZED', '401');
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
define('GC_DEFAULTS_CONFIG_LOADERS', 'config-loaders');
define('GC_DEFAULTS_CTRLEXPORTS_EXTENSIONS', 'ctrl-exports-extensions');
define('GC_DEFAULTS_DEBUG_PAGE', 'debug-page');
define('GC_DEFAULTS_DISABLED_DEBUGS', 'disabled-debugs');
define('GC_DEFAULTS_DISABLED_PATHS', 'disabled-paths');
define('GC_DEFAULTS_EMAIL_FROM', 'email-from');
define('GC_DEFAULTS_EMAIL_LAYOUT', 'email-layout');
define('GC_DEFAULTS_EMAIL_REPLAYTO', 'email-replayto');
define('GC_DEFAULTS_ERROR_PAGES', 'error-pages');
define('GC_DEFAULTS_EXCEPTION_PAGE', 'exception-page');
define('GC_DEFAULTS_FORMATS', 'formats');
define('GC_DEFAULTS_FORMS_TYPE', 'form-type');
define('GC_DEFAULTS_FORMS_TYPES', 'form-types');
define('GC_DEFAULTS_HTMLASSETS', 'html-assets');
define('GC_DEFAULTS_HTMLASSETS_SCRIPTS', 'scripts');
define('GC_DEFAULTS_HTMLASSETS_SPECIFICS', 'html-specific-assets');
define('GC_DEFAULTS_HTMLASSETS_STYLES', 'styles');
define('GC_DEFAULTS_INSTALLED', 'installed');
define('GC_DEFAULTS_LANGS_BUILT', 'langs-built');
define('GC_DEFAULTS_LANG', 'lang');
define('GC_DEFAULTS_LANGS_SESSIONSUFFIX', 'langs-suffix');
define('GC_DEFAULTS_LAYOUT', 'layout');
define('GC_DEFAULTS_MODES', 'modes');
define('GC_DEFAULTS_REDIRECTIONS', 'redirections');
define('GC_DEFAULTS_SERVICE', 'service');
define('GC_DEFAULTS_SERVICE_ALLOWEDBYSRV', 'service-allowed-by-srv');
define('GC_DEFAULTS_SERVICE_ALLOWEDSITES', 'service-allowed-sites');
define('GC_DEFAULTS_SHELLTOOLS_ALIASES', 'shelltools-aliases');
define('GC_DEFAULTS_SKIN', 'skin');
define('GC_DEFAULTS_SKIN_SESSIONSUFFIX', 'skin-suffix');
define('GC_DEFAULTS_VIEW_ADAPTER', 'view-adapter');
// @}
//
// Last run strucutre constants @{
define('GC_AFIELD_ACTION', 'action');
define('GC_AFIELD_ADAPTER', 'adapter');
define('GC_AFIELD_AFTER_CREATE', 'after_create');
define('GC_AFIELD_AFTER_DROP', 'after_drop');
define('GC_AFIELD_AFTER_UDPATE', 'after_update');
define('GC_AFIELD_ALIAS', 'alias');
define('GC_AFIELD_ASSIGNMENTS', 'assignments');
define('GC_AFIELD_BEFORE_CREATE', 'before_create');
define('GC_AFIELD_BEFORE_DROP', 'before_drop');
define('GC_AFIELD_BEFORE_UPDATE', 'before_update');
define('GC_AFIELD_BOTTOM', 'bottom');
define('GC_AFIELD_BUTTON', 'bottom');
define('GC_AFIELD_BUTTONS', 'bottoms');
define('GC_AFIELD_CACHE_PARAMS', 'cache_params');
define('GC_AFIELD_CACHED', 'cached');
define('GC_AFIELD_CALLBACKS', 'callbacks');
define('GC_AFIELD_CLASS', 'class');
define('GC_AFIELD_CODE', 'code');
define('GC_AFIELD_COMPILATIONS', 'compilations');
define('GC_AFIELD_CONNECTION', 'connection');
define('GC_AFIELD_CORS', 'CORS');
define('GC_AFIELD_COUNTS', 'counts');
define('GC_AFIELD_DATA', 'data');
define('GC_AFIELD_DEFAULT', 'default');
define('GC_AFIELD_DEFAULTS', 'defaults');
define('GC_AFIELD_DISABLED', 'disabled');
define('GC_AFIELD_DB', 'db');
define('GC_AFIELD_DESCRIPTION', 'description');
define('GC_AFIELD_END', 'end');
define('GC_AFIELD_ERROR', 'error');
define('GC_AFIELD_ERRORS', 'errors');
define('GC_AFIELD_EXTENSION', 'extension');
define('GC_AFIELD_FIELD', 'field');
define('GC_AFIELD_FIELDS', 'fields');
define('GC_AFIELD_FILE', 'file');
define('GC_AFIELD_FILES', 'files');
define('GC_AFIELD_FLAG', 'flag');
define('GC_AFIELD_FULL_RENDER', 'full-render');
define('GC_AFIELD_GENERATOR', 'generator');
define('GC_AFIELD_HASH', 'hash');
define('GC_AFIELD_HEADERS', 'headers');
define('GC_AFIELD_HITS', 'hits');
define('GC_AFIELD_HOST', 'host');
define('GC_AFIELD_HOSTS', 'hosts');
define('GC_AFIELD_ID', 'id');
define('GC_AFIELD_INDEXES', 'indexes');
define('GC_AFIELD_INFO', 'info');
define('GC_AFIELD_INTERFACE', 'interface');
define('GC_AFIELD_IGNORED', 'ignored');
define('GC_AFIELD_ITEMS', 'items');
define('GC_AFIELD_KEY', 'key');
define('GC_AFIELD_KEYS', 'keys');
define('GC_AFIELD_KEYS_BY_LANG', 'keys-by-lang');
define('GC_AFIELD_LANGS', 'langs');
define('GC_AFIELD_LANGS_PATH', 'langs-path');
define('GC_AFIELD_LASTERROR', 'lasterror');
define('GC_AFIELD_LAYOUT', 'layout');
define('GC_AFIELD_LEFT', 'left');
define('GC_AFIELD_LINE', 'line');
define('GC_AFIELD_LOCATION', 'location');
define('GC_AFIELD_MESSAGE', 'message');
define('GC_AFIELD_METHOD', 'method');
define('GC_AFIELD_METHODS', 'methods');
define('GC_AFIELD_MIDDLE', 'middle');
define('GC_AFIELD_MODULE_NAME', 'module-name');
define('GC_AFIELD_NAME', 'name');
define('GC_AFIELD_NULL', 'null');
define('GC_AFIELD_ORIGIN', 'origin');
define('GC_AFIELD_ORIGINS', 'origins');
define('GC_AFIELD_PARAMS', 'params');
define('GC_AFIELD_PARENT_DIRECTORY', 'parent-directory');
define('GC_AFIELD_PATH', 'path');
define('GC_AFIELD_POSSIBILITIES', 'possibilities');
define('GC_AFIELD_PRECISION', 'precision');
define('GC_AFIELD_QUERY', 'query');
define('GC_AFIELD_REDIRECTOR', 'redirector');
define('GC_AFIELD_RENDER', 'render');
define('GC_AFIELD_REQUIRED_PARAMS', 'required_params');
define('GC_AFIELD_RESOURCE', 'resource');
define('GC_AFIELD_RESULT', 'result');
define('GC_AFIELD_RIGHT', 'right');
define('GC_AFIELD_ROUTES_PATH', 'routes-path');
define('GC_AFIELD_SEQNAME', 'seqname');
define('GC_AFIELD_SERVICES', 'services');
define('GC_AFIELD_SKIN', 'skin');
define('GC_AFIELD_SPEC', 'spec');
define('GC_AFIELD_SPECS', 'specs');
define('GC_AFIELD_START', 'start');
define('GC_AFIELD_STATUS', 'status');
define('GC_AFIELD_SUBFOLDERS', 'subfolders');
define('GC_AFIELD_TABLES', 'tables');
define('GC_AFIELD_TEMPLATE', 'template');
define('GC_AFIELD_TOP', 'top');
define('GC_AFIELD_TRANSACTION', 'transaction');
define('GC_AFIELD_TYPE', 'type');
define('GC_AFIELD_TYPES', 'types');
define('GC_AFIELD_VALUES', 'values');
// @}
//
// Directories @{
define('GC_DIRECTORIES_CACHE', 'cache');
define('GC_DIRECTORIES_CONFIG_INTERPRETERS', 'config-interpreters');
define('GC_DIRECTORIES_CONFIGS', 'configs');
define('GC_DIRECTORIES_FORMS', 'forms');
define('GC_DIRECTORIES_INCLUDES', 'includes');
define('GC_DIRECTORIES_LIBRARIES', 'libraries');
define('GC_DIRECTORIES_REPRESENTATIONS', 'representations');
define('GC_DIRECTORIES_MANAGERS', 'managers');
define('GC_DIRECTORIES_ADAPTERS', 'adapters');
define('GC_DIRECTORIES_ADAPTERS_CACHE', 'adapters-cache');
define('GC_DIRECTORIES_ADAPTERS_DB', 'adapters-db');
define('GC_DIRECTORIES_ADAPTERS_VIEW', 'adapters-view');
define('GC_DIRECTORIES_MODULES', 'modules');
define('GC_DIRECTORIES_REST', 'rest');
define('GC_DIRECTORIES_SAPIREADER', 'sapireader');
define('GC_DIRECTORIES_SEARCH', 'search');
define('GC_DIRECTORIES_SHELL_INCLUDES', 'shell-includes');
define('GC_DIRECTORIES_SHELL_FLAGS', 'shell-flags');
define('GC_DIRECTORIES_SITE', 'site');
define('GC_DIRECTORIES_SPECS', 'specs');
define('GC_DIRECTORIES_SYSTEM', 'system');
define('GC_DIRECTORIES_SYSTEM_CACHE', 'system-cache');
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
define('GC_CONNECTIONS_DEFAULTS', 'defaults');
define('GC_CONNECTIONS_DEFAULTS_DB', 'db');
define('GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS', 'keep-unknowns');
define('GC_CONNECTIONS_DEFAULTS_INSTALL', 'dbinstall');
define('GC_CONNECTIONS_DEFAULTS_CACHE', 'cache');
// @}
//
// Database structure @{
define('GC_DATABASE_DB_CONNECTION_ADAPTERS', 'db-connection-adapters');
define('GC_DATABASE_DB_QUERY_ADAPTERS', 'db-query-adapters');
define('GC_DATABASE_DB_SPEC_ADAPTERS', 'db-spec-adapters');
define('GC_DATABASE_DB_VERSION_ADAPTERS', 'db-version-adapters');
define('GC_DATABASE_DEFAULT_SPECS', 'default-specs');
define('GC_DATABASE_FIELD_FILTER_BOOLEAN', 'boolean');
define('GC_DATABASE_FIELD_FILTER_JSON', 'json');
define('GC_DATABASE_FIELD_FILTERS', 'field-filters');
// @}
//
// Representations @{
define('GC_REPRESENTATIONS_COLUMN', 'column');
define('GC_REPRESENTATIONS_FACTORY', 'factory');
define('GC_REPRESENTATIONS_FACTORY_SHORTCUT', 'factory_shortcut');
define('GC_REPRESENTATIONS_METHOD', 'method');
define('GC_REPRESENTATIONS_METHOD_IDS', 'id_method');
define('GC_REPRESENTATIONS_METHOD_ITEM', 'item_method');
define('GC_REPRESENTATIONS_METHOD_ITEMS', 'items_method');
define('GC_REPRESENTATIONS_PLURAL', 'plural');
// @}
//
// URIs @{
define('GC_URIS_INCLUDES', 'includes');
// @}
//
// Paths @{
define('GC_PATHS_CONFIGS', 'configs');
define('GC_PATHS_CONTROLLERS', 'controllers');
define('GC_PATHS_CSS', 'css');
define('GC_PATHS_DBSPECS', 'dbspecs');
define('GC_PATHS_DBSPECSCALLBACK', 'dbspecscallbacks');
define('GC_PATHS_EMAIL_CONTROLLERS', 'email-controllers');
define('GC_PATHS_FORMS', 'forms');
define('GC_PATHS_IMAGES', 'images');
define('GC_PATHS_JS', 'js');
define('GC_PATHS_LANGS', 'langs');
define('GC_PATHS_LAYOUTS', 'layouts');
define('GC_PATHS_MODELS', 'models');
define('GC_PATHS_REPRESENTATIONS', 'representations');
define('GC_PATHS_SAPIREADER', 'sapireader');
define('GC_PATHS_SAPIREPORTS', 'sapireports');
define('GC_PATHS_SERVICES', 'services');
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
define('GC_SESSION_LANGUAGE', 'toobasic-lang');
// @}
//
// Request parameters @{
define('GC_REQUEST_ACTION', 'action');
define('GC_REQUEST_EXTRA_ROUTE', '_route');
define('GC_REQUEST_LANGUAGE', 'lang');
define('GC_REQUEST_LAYOUT', 'layout');
define('GC_REQUEST_MODE', 'mode');
define('GC_REQUEST_REDIRECTOR', 'redirectedfrom');
define('GC_REQUEST_REST', 'rest');
define('GC_REQUEST_ROUTE', 'route');
define('GC_REQUEST_SERVICE', 'service');
define('GC_REQUEST_SKIN', 'skin');
// @}
//
// Server parameters @{
define('GC_SERVER_TOOBASIC_ROUTE', 'TOOBASIC_ROUTE');
// @}
//
// Class Suffixes @{
define('GC_CLASS_SUFFIX_CONFIG', 'Config');
define('GC_CLASS_SUFFIX_CONTROLLER', 'Controller');
define('GC_CLASS_SUFFIX_CORE_PROPS', 'CoreProps');
define('GC_CLASS_SUFFIX_CRON', 'Cron');
define('GC_CLASS_SUFFIX_EMAIL_CONTROLLER', 'Email');
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
//
// Memcached parameters @{
define('GC_DEFAULTS_MEMCACHE', 'memcache');
define('GC_DEFAULTS_MEMCACHE_SERVER', 'server');
define('GC_DEFAULTS_MEMCACHE_PORT', 'port');
define('GC_DEFAULTS_MEMCACHE_PREFIX', 'prefix');
define('GC_DEFAULTS_MEMCACHE_COMPRESSED', 'compressed');
// @}
//
// Database query adapters prefixes @{
define('GC_DBQUERY_NAMES_COLUMN_ID', 'column-id');
define('GC_DBQUERY_NAMES_COLUMN_NAME', 'column-name');
define('GC_DBQUERY_PREFIX_COLUMN', 'column');
define('GC_DBQUERY_PREFIX_TABLE', 'table');
// @}
//
// Magic Properties @{
define('GC_MAGICPROP_PROPERTIES', 'properties');
define('GC_MAGICPROP_ALIASES', 'aliases');
define('GC_MAGICPROP_PROP_CACHE', 'cache');
define('GC_MAGICPROP_PROP_CONFIG', 'config');
define('GC_MAGICPROP_PROP_MODEL', 'model');
define('GC_MAGICPROP_PROP_PARAMS', 'params');
define('GC_MAGICPROP_PROP_PATHS', 'paths');
define('GC_MAGICPROP_PROP_REPRESENTATION', 'representation');
define('GC_MAGICPROP_PROP_SAPIREADER', 'sapireader');
define('GC_MAGICPROP_PROP_TR', 'tr');
define('GC_MAGICPROP_PROP_TRANSLATE', 'translate');
// @}
//
// Simple API Reader @{
define('GC_SAPIREADER_DEFAULT_TYPE', 'default');
define('GC_SAPIREADER_TYPES', 'types');
define('GC_SAPIREADER_TYPE_BASIC', 'basic');
define('GC_SAPIREADER_TYPE_JSON', 'json');
define('GC_SAPIREADER_TYPE_XML', 'xml');

define('GC_SAPIREPORT_TYPES', 'sareport-types');
define('GC_SAPIREPORT_TYPE_BASIC', 'basic');
define('GC_SAPIREPORT_TYPE_BOOTSTRAP', 'bootstrap');

define('GC_SAPIREPORT_COLUMNTYPE_BUTTONLINK', 'button-link');
define('GC_SAPIREPORT_COLUMNTYPE_CODE', 'code');
define('GC_SAPIREPORT_COLUMNTYPE_IMAGE', 'image');
define('GC_SAPIREPORT_COLUMNTYPE_LINK', 'link');
define('GC_SAPIREPORT_COLUMNTYPE_TEXT', 'text');
// @}
//
// TooBasic's search engine @{
define('GC_SEARCH_ENGINE_FACTORIES', 'factories');
// @}
//
// RESTful constants @{
define('GC_REST_DEFAULT_KEY', '__DEFAULT__');
define('GC_REST_TYPE_RESOURCE', 'resource');
define('GC_REST_TYPE_SEARCH', 'search');
define('GC_REST_TYPE_STATS', 'stats');
define('GC_REST_POLICY_BLOCKED', 'blocked');
define('GC_REST_POLICY_AUTH', 'auth');
define('GC_REST_POLICY_ACTIVE', 'active');
// @}
//
// Forms @{
//
// Build flags.
define('GC_FORMS_BUILDTYPE_BASIC', 'basic');
define('GC_FORMS_BUILDTYPE_BOOTSTRAP', 'bootstrap');
define('GC_FORMS_BUILDTYPE_TABLE', 'table');
//
// Build flags.
define('GC_FORMS_BUILDFLAG_SPACER', 'spacer');
//
// Build mode.
define('GC_FORMS_BUILDMODE_CREATE', 'create');
define('GC_FORMS_BUILDMODE_EDIT', 'edit');
define('GC_FORMS_BUILDMODE_REMOVE', 'remove');
define('GC_FORMS_BUILDMODE_VIEW', 'view');
//
// Field type.
define('GC_FORMS_FIELDTYPE_ENUM', 'enum');
define('GC_FORMS_FIELDTYPE_HIDDEN', 'hidden');
define('GC_FORMS_FIELDTYPE_INPUT', 'input');
define('GC_FORMS_FIELDTYPE_PASSWORD', 'password');
define('GC_FORMS_FIELDTYPE_TEXT', 'text');
//
// Button types.
define('GC_FORMS_BUTTONTYPE_BUTTON', 'button');
define('GC_FORMS_BUTTONTYPE_RESET', 'reset');
define('GC_FORMS_BUTTONTYPE_SUBMIT', 'submit');
// @}
//
// Config modes.
define('GC_CONFIG_MODE_MERGE', 'merge');
define('GC_CONFIG_MODE_MULTI', 'multi');
define('GC_CONFIG_MODE_SIMPLE', 'simple');
// @}
//
// Smarty specifics.
define('GC_SMARTY_LEFT_DELIMITER', 'smarty-left-delimiter');
define('GC_SMARTY_RIGHT_DELIMITER', 'smarty-right-delimiter');
// @}
// @}
