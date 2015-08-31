<?php

/**
 * @file index.php
 * @author Alejandro Dario Simi
 */
include __DIR__.'/config/config.php';

use \TooBasic\Params;
use \TooBasic\Managers\ServicesManager as ServicesManager;
use \TooBasic\Managers\ActionsManager as ActionsManager;
use \TooBasic\Managers\EmailsManager as EmailsManager;

try {
	if(isset(Params::Instance()->debugphpinfo)) {
		\TooBasic\debugThing('phpinfo');
		die;
	}

	if(isset(Params::Instance()->get->debugemail)) {
		if(Params::Instance()->get->debugemail) {
			$manager = EmailsManager::Instance();
			$manager->run(true);
		} else {
			throw new \TooBasic\Exception("No email name given, try '?debugemail=my_email'");
		}
	} elseif($ServiceName || isset(Params::Instance()->get->explaininterface)) {
		ServicesManager::Instance()->run();
	} else {
		ActionsManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayWebPage($e);
}
