<?php

/**
 * @file shell.php
 * @author Alejandro Dario Simi
 */
include __DIR__.'/config/config.php';

use TooBasic\Managers\ShellManager as ShellManager;

try {
	if(!defined('__SHELL__')) {
		echo 'This is a shell only script';
	} else {
		ShellManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayShellMessage($e);
}
