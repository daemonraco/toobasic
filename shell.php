<?php

/**
 * @file shell.php
 * @author Alejandro Dario Simi
 */
use TooBasic\Managers\ShellManager;

try {
	include __DIR__.'/config/config.php';

	if(!defined('__SHELL__')) {
		echo 'This is a shell only script';
	} else {
		ShellManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayShellMessage($e);
}
