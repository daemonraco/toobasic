<?php

include dirname(__DIR__)."/config/config.php";
if(!defined("__SHELL__")) {
	echo "This is a shell only script";
} else {
	ShellManager::Instance()->run();
}