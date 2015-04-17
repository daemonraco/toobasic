<?php

use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Translate as TB_Translate;

class TranslateCron extends TooBasic\Shell\ShellCron {
	//
	// Constants.
	const OptionCompile = "Compile";
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText("This tool provides a way to perform certain batch task related with translations.");

		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCompile, array("-c", "--compile"), TBS_Option::TypeNoValue));
	}
	protected function taskCompile($spacer = "") {
		echo "{$spacer}Compiling languages:\n";

		$result = TB_Translate::Instance()->compileLangs();

		echo "{$spacer}\tAffected keys: {$result["counts"]["keys"]}\n";

		echo "{$spacer}\tLanguages:\n";
		foreach($result["langs"] as $lang) {
			echo "{$spacer}\t\t- '{$lang}': {$result["counts"]["keys-by-lang"][$lang]} keys\n";
		}

		echo "{$spacer}\tAnalysed files:\n";
		foreach($result["files"] as $lang => $files) {
			echo "{$spacer}\t\t- '{$lang}':\n";
			foreach($files as $file) {
				echo "{$spacer}\t\t\t- '{$file}'\n";
			}
		}

		echo "{$spacer}\tGenerated files:\n";
		foreach($result["compilations"] as $file) {
			echo "{$spacer}\t\t- '{$file}'\n";
		}
	}
	protected function taskInfo($spacer = "") {
		
	}
}
