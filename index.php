<?php

include __DIR__.'/config/config.php';

if(isset($_REQUEST['debugphpinfo'])) {
	\TooBasic\debugThing('phpinfo');
	die;
}

if($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
	TooBasic\ServicesManager::Instance()->run();
} else {
	TooBasic\ActionsManager::Instance()->run();
}
