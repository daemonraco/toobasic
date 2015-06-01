<?php

if(isset($_REQUEST['debugphpinfo'])) {
	phpinfo();
	die;
}

include __DIR__.'/config/config.php';

if($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
	TooBasic\ServicesManager::Instance()->run();
} else {
	TooBasic\ActionsManager::Instance()->run();
}
