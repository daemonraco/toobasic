<?php

include __DIR__."/config/config.php";

if($ServiceName) {
	ServicesManager::Instance()->run();
} else {
	ActionsManager::Instance()->run();
}
