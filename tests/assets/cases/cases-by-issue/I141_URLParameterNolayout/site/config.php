<?php

$Defaults[GC_DEFAULTS_LAYOUT] = 'layout';
$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;

if(is_file(__DIR__.'/configs/nodebugs.php')) {
	require_once __DIR__.'/configs/nodebugs.php';
}
