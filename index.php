<?php

include __DIR__."/config/config.php";

if($ServiceName) {
	TooBasic\ServicesManager::Instance()->run();
} else {
	TooBasic\ActionsManager::Instance()->run();
}
