<?php

include __DIR__.'/config/config.php';

try {
	if(isset(\TooBasic\Params::Instance()->debugphpinfo)) {
		\TooBasic\debugThing('phpinfo');
		die;
	}

	if($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
		\TooBasic\ServicesManager::Instance()->run();
	} else {
		\TooBasic\ActionsManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayWebPage($e);
}
