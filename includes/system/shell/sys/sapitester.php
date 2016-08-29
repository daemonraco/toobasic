<?php

use TooBasic\Paths;
use TooBasic\Shell\Option;
use TooBasic\Shell\Color;
use TooBasic\SApiReaderAbstractException;
use TooBasic\SApiReaderException;

class SapitesterSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionCall = 'Call';
	const OptionCheck = 'Check';
	const OptionList = 'List';
	const OptionSpecific = 'Specific';
	//
	// Protected methods.
	protected function setOptions() {
		$this->_options->setHelpText("TODO tool summary");

		$text = "TODO help text for: '--call', '-c'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionCall, array('--call', '-c'), Option::TypeNoValue, $text, 'value'));

		$text = "TODO help text for: '--check', '-C'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionCheck, array('--check', '-C'), Option::TypeValue, $text, 'value'));

		$text = "This option prompts a detailed list of available SimpleAPI configurations.";
		$this->_options->addOption(Option::EasyFactory(self::OptionList, array('--list', '-l'), Option::TypeNoValue, $text));

		$text = "This option can be used along with '--call' to prompt a more detailed result dump.\n";
		$text = "Basically 'var_dump()' instead of 'print_r()'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSpecific, array('--specific', '-s'), Option::TypeNoValue, $text));
	}
	protected function taskCall($spacer = "") {
		$ok = true;
		$error = '';

		$params = $this->_options->unknownParams();
		$api = false;
		$apiConfig = false;
		$apiName = false;
		$apiMethod = false;

		if($ok) {
			if(isset($params[0])) {
				$apiName = array_shift($params);
			} else {
				$ok = false;
				$error = 'No Simple API code specified';
			}
		}
		if($ok) {
			try {
				$api = $this->sapireader->{$apiName};
				$apiConfig = $api->config();
			} catch(SApiReaderException $e) {
				$ok = false;
				$error = $e->getMessage();
			}
		}
		if($ok) {
			if(isset($params[0])) {
				$apiMethod = array_shift($params);
			} else {
				$ok = false;
				$error = 'No Simple API method specified';
			}
		}
		if($ok) {
			if(!isset($apiConfig->services->{$apiMethod})) {
				$ok = false;
				$error = "Method '{$apiMethod}()' for Simple API configuration '{$apiName}' is not defined";
			}
		}

		if($ok) {
			$virtualCmd = "{$apiName}->{$apiMethod}(";
			if($params) {
				$virtualCmd.= "'".implode("', '", $params)."'";
			}
			$virtualCmd.= ")";

			echo "{$spacer}Running '".Color::Green($virtualCmd)."':\n";

			try {
				eval("\$result=\$this->sapireader->{$virtualCmd};");

				ob_start();
				if($this->_options->option(self::OptionSpecific)->activated()) {
					var_dump($result);
				} else {
					print_r($result);
				}
				$out = ob_get_contents();
				ob_end_clean();

				$out = explode("\n", $out);
				array_walk($out, function(&$item) {
					$item = Color::Yellow("  > ").$item;
				});
				$out = implode("\n", $out)."\n";

				echo $out;
			} catch(SApiReaderException $e) {
				$ok = false;
				$error = $e->getMessage();
			}
		}

		if(!$ok) {
			echo Color::Red("{$spacer}{$error}\n");
		}

		return $ok;
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

				echo "{$spacer}\t- '".Color::Green($pathInfo['filename'])."':\n";

				try {
					$sapi = $this->sapireader->{$pathInfo['filename']};
				} catch(SApiReaderAbstractException $e) {
					$sapi = false;
					echo "{$spacer}\t\t".Color::Yellow('abstract specification')."\n";
				}

				if($sapi) {
					$config = $sapi->config();

					echo "{$spacer}\t\t- Name:        {$config->name}\n";
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
