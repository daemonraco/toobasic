<?php

$Defaults[GC_DEFAULTS_LAYOUT] = 'layout';

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';

$Connections[GC_CONNECTIONS_DB]['test'] = array(
	GC_CONNECTIONS_DB_ENGINE => 'pgsql',
	GC_CONNECTIONS_DB_SERVER => 'localhost',
	GC_CONNECTIONS_DB_PORT => false,
	GC_CONNECTIONS_DB_NAME => '%TRAVISCI_PSQL_DBNAME%',
	GC_CONNECTIONS_DB_USERNAME => '%TRAVISCI_PSQL_USERNAME%',
	GC_CONNECTIONS_DB_PASSWORD => '%TRAVISCI_PSQL_PASSWORD%',
	GC_CONNECTIONS_DB_PREFIX => 'tst_'
);
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB] = 'test';
