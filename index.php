<?php

include __DIR__.'/config/config.php';

if(isset(TooBasic\Params::Instance()->debugphpinfo)) {
	\TooBasic\debugThing('phpinfo');
	die;
}

try {
	if($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
		TooBasic\ServicesManager::Instance()->run();
	} else {
		TooBasic\ActionsManager::Instance()->run();
	}
} catch(Exception $e) {
	\TooBasic\debugThing(array('Uncaught exception' => $e), \TooBasic\DebugThingTypeError);
	die;
}
