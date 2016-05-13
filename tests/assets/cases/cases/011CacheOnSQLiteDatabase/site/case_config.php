<?php

//
// Database
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS] = true;
//
// Cache
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\DBSQLite';
$Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_CACHE] = 'test';
