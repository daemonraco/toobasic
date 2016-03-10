<?php

define('TESTS_TESTS_ASSETS_DIR', __DIR__);
define('TESTS_TESTS_DIR', dirname(TESTS_TESTS_ASSETS_DIR));

define('TESTS_TESTS_ACASES_DIR', TESTS_TESTS_ASSETS_DIR.'/cases');
define('TESTS_TESTS_AINCLUDES_DIR', TESTS_TESTS_ASSETS_DIR.'/includes');

define('TESTS_NEWCASE_DIR', TESTS_TESTS_ASSETS_DIR.'/newcase');

define('TESTS_NEWCASE_TEMPLATES', TESTS_NEWCASE_DIR.'/templates');
define('TESTS_CACHE_DIR', TESTS_ROOTDIR.'/cache');
define('TESTS_SMARTY_DIR', TESTS_ROOTDIR.'/cache/smarty');

define('TESTS_LAST_GENERATION', TESTS_CACHE_DIR.'/newcase_lastgen.txt');

define('TEST_AFIELD_ASSETS_PATH', 'assets-path');
define('TEST_AFIELD_MAIN_MANIFEST_PATH', 'main-manifest-path');
define('TEST_AFIELD_MANIFEST_PATH', 'manifest-path');
define('TEST_AFIELD_CASE_NAME', 'case-name');
define('TEST_AFIELD_CASE_PATH', 'case-path');
define('TEST_AFIELD_CASE_TYPE', 'case-type');
