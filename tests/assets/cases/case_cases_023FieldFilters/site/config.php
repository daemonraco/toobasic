<?php

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;

$Connections[GC_CONNECTIONS_DB]['test'] = array(
	GC_CONNECTIONS_DB_ENGINE => 'mysql',
	GC_CONNECTIONS_DB_SERVER => 'localhost',
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => '%TRAVISCI_MYSQL_DBNAME%',
	GC_CONNECTIONS_DB_USERNAME => '%TRAVISCI_MYSQL_USERNAME%',
	GC_CONNECTIONS_DB_PASSWORD => '%TRAVISCI_MYSQL_PASSWORD%',
	GC_CONNECTIONS_DB_PREFIX => 'tst_'
);
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB] = 'test';
