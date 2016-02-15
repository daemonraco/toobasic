<?php

define('TOOBASIC_NEWCASE_DIR', __DIR__);
define('TOOBASIC_TESTS_ASSETS_DIR', dirname(TOOBASIC_NEWCASE_DIR));
define('TOOBASIC_TESTS_DIR', dirname(TOOBASIC_TESTS_ASSETS_DIR));
define('TOOBASIC_ROOTDIR', dirname(TOOBASIC_TESTS_DIR));

define('TOOBASIC_NEWCASE_TEMPLATES', TOOBASIC_NEWCASE_DIR.'/templates');
define('TOOBASIC_CACHE_DIR', TOOBASIC_ROOTDIR.'/cache');
define('TOOBASIC_SMARTY_DIR', TOOBASIC_ROOTDIR.'/cache/smarty');
define('TOOBASIC_TESTS_ACASES_DIR', TOOBASIC_TESTS_ASSETS_DIR.'/cases');
define('TOOBASIC_TESTS_AINCLUDES_DIR', TOOBASIC_TESTS_ASSETS_DIR.'/includes');

define('TOOBASIC_LAST_GENERATION', TOOBASIC_CACHE_DIR.'/newcase_lastgen.txt');

if(is_file(TOOBASIC_ROOTDIR.'/libraries/smarty/Smarty.class.php')) {
	require_once TOOBASIC_ROOTDIR.'/libraries/smarty/Smarty.class.php';
} elseif(is_file(TOOBASIC_ROOTDIR.'/libraries/smarty.git/libs/Smarty.class.php')) {
	require_once TOOBASIC_ROOTDIR.'/libraries/smarty.git/libs/Smarty.class.php';
} else {
	throw new Exception('Unable to find Smarty.');
}
