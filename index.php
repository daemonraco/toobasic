<?php

include __DIR__."/config/config.php";

if($ServiceName || isset(\TooBasic\Params::Instance()->get->explaininterface)) {
	TooBasic\ServicesManager::Instance()->run();
} else {
	TooBasic\ActionsManager::Instance()->run();
}
