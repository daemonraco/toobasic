<?php

$Defaults[GC_DEFAULTS_LAYOUT] = 'layout';

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';

$Connections[GC_CONNECTIONS_DB]['test'] = array(
	GC_CONNECTIONS_DB_ENGINE => 'sqlite',
	GC_CONNECTIONS_DB_SERVER => "{$Directories[GC_DIRECTORIES_CACHE]}/travis_test.sqlite3",
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => false,
	GC_CONNECTIONS_DB_USERNAME => false,
	GC_CONNECTIONS_DB_PASSWORD => false,
	GC_CONNECTIONS_DB_PREFIX => 'tst_'
);
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB] = 'test';