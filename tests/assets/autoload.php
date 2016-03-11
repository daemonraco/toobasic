<?php

require_once __DIR__.'/define.php';

//
// Loading TravisCI variables.
echo "Loading environment variables:\n";
$aux = $_ENV;
ksort($aux);
foreach($aux as $name => $value) {
	if(preg_match('/^TRAVISCI_/', $name)) {
		echo "\t- '\033[1;36m{$name}\033[0m' (value length: \033[1;33m".strlen($value)."\033[0m)\n";
		define($name, $value);
	}
}
unset($aux);

require_once TESTS_TESTS_AINCLUDES_DIR.'/TooBasic_AssetsManager.php';
require_once TESTS_TESTS_AINCLUDES_DIR.'/TooBasic_Helper.php';
require_once TESTS_TESTS_AINCLUDES_DIR.'/TooBasic_TestCase.php';
require_once TESTS_TESTS_AINCLUDES_DIR.'/TooBasic_SeleniumTestCase.php';
