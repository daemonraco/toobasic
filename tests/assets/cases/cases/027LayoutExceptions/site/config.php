<?php

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;

if(is_file(__DIR__.'/unknown_layout.php')) {
	require_once __DIR__.'/unknown_layout.php';
}
if(is_file(__DIR__.'/broken_layout.php')) {
	require_once __DIR__.'/broken_layout.php';
}
if(is_file(__DIR__.'/failing_layout.php')) {
	require_once __DIR__.'/failing_layout.php';
}
