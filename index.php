<?php

/**
 * @file index.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Managers\ActionsManager;
use TooBasic\Managers\EmailsManager;
use TooBasic\Managers\RestManager;
use TooBasic\Managers\ServicesManager;
use TooBasic\Params;
use TooBasic\Translate;

try {
	include __DIR__.'/config/config.php';

	if(isset(Params::Instance()->debugphpinfo)) {
		\TooBasic\debugThingInPage('phpinfo', 'PHP Sites Information');
	}

	if(isset(Params::Instance()->get->debugemail)) {
		if(Params::Instance()->get->debugemail) {
			$manager = EmailsManager::Instance();
			$manager->run(true);
		} else {
			throw new \TooBasic\Exception(Translate::Instance()->EX_no_email_name_given);
		}
	} elseif($RestPath) {
		RestManager::Instance()->run();
	} elseif($ServiceName || isset(Params::Instance()->get->explaininterface)) {
		ServicesManager::Instance()->run();
	} else {
		ActionsManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayWebPage($e);
}
