<?php

/**
 * @file shell.php
 * @author Alejandro Dario Simi
 */
include __DIR__.'/config/config.php';

try {
	if(!defined('__SHELL__')) {
		echo 'This is a shell only script';
	} else {
		TooBasic\ShellManager::Instance()->run();
	}
} catch(\Exception $e) {
	\TooBasic\Exception::DisplayShellMessage($e);
}
