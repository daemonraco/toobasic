<?php

require_once dirname(dirname(__DIR__)).'/bootstrap.php';

if(is_file(TESTS_ROOTDIR.'/libraries/smarty/Smarty.class.php')) {
	require_once TESTS_ROOTDIR.'/libraries/smarty/Smarty.class.php';
} elseif(is_file(TESTS_ROOTDIR.'/libraries/smarty.git/libs/Smarty.class.php')) {
	require_once TESTS_ROOTDIR.'/libraries/smarty.git/libs/Smarty.class.php';
} else {
	throw new Exception('Unable to find Smarty.');
}
