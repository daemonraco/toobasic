<?php

/**
 * @file index.php
 * @author Alejandro Dario Simi
 */
include __DIR__.'/config/config.php';

try {
	if(isset(\TooBasic\Params::Instance()->debugphpinfo)) {
		\TooBasic\debugThing('phpinfo');
		die;
	}

	if(isset(\TooBasic\Params::Instance()->get->debugemail)) {
		if(\TooBasic\Params::Instance()->get->debugemail) {
			$manager = \TooBasic\Managers\EmailsManager::Instance();
			$manager->run(true);
		} else {
			throw new \TooBasic\Exception("No email name given, try '?debugemail=my_email'");
		}
	} elseif($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
		\TooBasic\ServicesManager::Instance()->run();
	} else {
		\TooBasic\ActionsManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayWebPage($e);
}
