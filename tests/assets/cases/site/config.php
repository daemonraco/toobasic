<?php

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;

foreach(['installed_config', 'mysql_config', 'pgsql_config', 'sqlite_config', 'case_config'] as $file) {
	$path = __DIR__."/{$file}.php";
	if(is_file($path)) {
		require_once $path;
	}
}
