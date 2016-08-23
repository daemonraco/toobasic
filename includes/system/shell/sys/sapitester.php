<?php

use TooBasic\Paths;
use TooBasic\Shell\Option;
use TooBasic\Shell\Color;
use TooBasic\SApiReaderException;

class SapitesterSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionList = 'List';
	const OptionCheck = 'Check';
	const OptionCall = 'Call';
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText("TODO tool summary");

		$text = "TODO help text for: '--call', '-C'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionCall, array('--call', '-C'), Option::TypeNoValue, $text, 'value'));

		$text = "TODO help text for: '--check', '-c'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionCheck, array('--check', '-c'), Option::TypeValue, $text, 'value'));

		$text = "This option prompts a detailed list of available SimpleAPI configurations.";
		$this->_options->addOption(Option::EasyFactory(self::OptionList, array('--list', '-l'), Option::TypeNoValue, $text));
	}
	protected function taskCall($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskCheck($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskList($spacer = "") {
		//
		// Global dependencies.
		global $Paths;

		$paths = $this->paths->customPaths($Paths[GC_PATHS_SAPIREADER], '*', Paths::ExtensionJSON, true);
		if($paths) {
			echo "{$spacer}These is the list of available SimpleAPI configurations:\n";

			foreach($paths as $path) {
				$pathInfo = pathinfo($path);
				$sapi = false;
				try {
					$sapi = $this->sapireader->{$pathInfo['filename']};
				} catch(SApiReaderException $e) {
					// . . .
				}

				if($sapi) {
					$config = $sapi->config();

					echo "{$spacer}\t- '".Color::Green($config->name)."':\n";

					echo "{$spacer}\t\t- Code:        ".Color::Green($pathInfo['filename'])."\n";
					echo "{$spacer}\t\t- Description: {$config->description}\n";
					echo "{$spacer}\t\t- Base URL:    ".Color::Yellow($config->url)."\n";
					echo "{$spacer}\t\t- Type:        ".Color::Yellow($config->type)."\n";
					if($config->headers) {
						echo "{$spacer}\t\t- Headers:\n";
						foreach($config->headers as $name => $value) {
							echo "{$spacer}\t\t\t- ".Color::Yellow($name).": '{$value}'\n";
						}
					}

					echo "{$spacer}\t\t- Services:\n";
					foreach($config->services as $name => $conf) {
						echo "{$spacer}\t\t\t- ".Color::Yellow("{$name}()").":\n";
						echo "{$spacer}\t\t\t\t- Method:     ".Color::Yellow($conf->method)."\n";
						echo "{$spacer}\t\t\t\t- URI:        ".Color::Yellow($conf->uri)."\n";

						if($conf->params) {
							echo "{$spacer}\t\t\t\t- Parameters:\n";
							foreach($conf->params as $param) {
								echo "{$spacer}\t\t\t\t\t- '".Color::Yellow($param)."'\n";
							}
						}
						if(get_object_vars($conf->sendParams)) {
							echo "{$spacer}\t\t\t\t- Send Parameters:\n";
							foreach($conf->sendParams as $param => $value) {
								echo "{$spacer}\t\t\t\t\t- '".Color::Yellow($param)."': '".Color::Yellow($value)."'\n";
							}
						}
						if(get_object_vars($conf->defaults)) {
							echo "{$spacer}\t\t\t\t- default Values:\n";
							foreach($conf->defaults as $param => $value) {
								echo "{$spacer}\t\t\t\t\t- '".Color::Yellow($param)."': '".Color::Yellow($value)."'\n";
							}
						}
					}
				}
			}

			echo "\n";
		} else {
			echo "{$spacer}There are no available SimpleAPI configurations.\n";
		}
	}
}
