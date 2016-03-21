<?php

$Defaults[GC_DEFAULTS_CACHE_ADAPTER] = '\\TooBasic\\Adapters\\Cache\\NoCache';
$Defaults[GC_DEFAULTS_ALLOW_ROUTES] = true;

$SuperLoader['MySingleton'] = __DIR__."/includes/MySingleton.php";

$MagicProps[GC_MAGICPROP_PROPERTIES]['ms'] = 'MySingleton';
$MagicProps[GC_MAGICPROP_ALIASES]['mys'] = 'ms';
