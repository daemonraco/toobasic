<?php

/**
 * @file translate.php
 * @author Alejandro Dario Simi
 */
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Translate as TB_Translate;

/**
 * @class TranslateTool
 */
class TranslateTool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OPTION_COMPILE = 'Compile';
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText('This tool provides a way to perform certain batch task related with translations.');

		$this->_options->addOption(TBS_Option::EasyFactory(self::OPTION_COMPILE, ['-c', '--compile'], TBS_Option::TypeNoValue));
	}
	protected function taskCompile($spacer = '') {
		echo "{$spacer}Compiling languages:\n";

		$result = TB_Translate::Instance()->compileLangs();

		echo "{$spacer}\tAffected keys: {$result[GC_AFIELD_COUNTS][GC_AFIELD_KEYS]}\n";

		echo "{$spacer}\tLanguages:\n";
		foreach($result[GC_AFIELD_LANGS] as $lang) {
			echo "{$spacer}\t\t- '{$lang}': {$result[GC_AFIELD_COUNTS][GC_AFIELD_KEYS_BY_LANG][$lang]} keys\n";
		}

		echo "{$spacer}\tAnalysed files:\n";
		foreach($result[GC_AFIELD_FILES] as $lang => $files) {
			echo "{$spacer}\t\t- '{$lang}':\n";
			foreach($files as $file) {
				echo "{$spacer}\t\t\t- '{$file}'\n";
			}
		}

		echo "{$spacer}\tGenerated files:\n";
		foreach($result[GC_AFIELD_COMPILATIONS] as $file) {
			echo "{$spacer}\t\t- '{$file}'\n";
		}
	}
	protected function taskInfo($spacer = "") {
		
	}
}
