<?php

/**
 * @file ShellManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

use TooBasic\Shell\Options;

/**
 * @class ShellManager
 */
class ShellManager extends Manager {
	//
	// Constants.
	const ErrorNoMode = 1;
	const ErrorNotValidMode = 2;
	const ErrorNoToolName = 3;
	const ErrorNoToolClass = 4;
	const ErrorUnknownTool = 5;
	const ErrorNoProfileName = 6;
	const ErrorUnknownProfile = 7;
	const ModeCron = 'cron';
	const ModeProfile = 'profile';
	const ModeTool = 'tool';
	const ModeSys = 'sys';
	//
	// Protected properties.
	protected $_errors = array();
	protected $_mode = false;
	protected $_script = false;
	protected $_profile = false;
	protected $_tool = false;
	protected $_toolClass = null;
	//
	// Public methods.
	public function errors() {
		return $this->_errors;
	}
	public function hasErrors() {
		return count($this->errors()) > 0;
	}
	public function run($spacer = '') {
		//
		// Checking for start errors.
		if(!$this->hasErrors()) {
			$options = $this->starterOptions();
			$options->check();

			$this->_script = $options->script;
			$this->_mode = $options->mode;
			$this->_tool = $options->tool;

			switch($this->_mode) {
				case self::ModeProfile:
					$this->_profile = $this->_tool;
					$this->_tool = false;
					$this->runProfile($spacer, $options->unknownParams());
					break;
				case self::ModeTool:
					$this->runTool($spacer);
					break;
				case self::ModeCron:
					$this->runCron($spacer);
					break;
				case self::ModeSys:
					$this->runSys($spacer);
					break;
				default:
					if($this->_mode) {
						$this->setError(self::ErrorNotValidMode, "Mode '{$this->_mode}' is not valid");
					} else {
						$this->setError(self::ErrorNoMode, 'No mode specified');
					}

					echo "{$spacer}Available modes are:\n";
					echo "{$spacer}\t- ".self::ModeTool."\n";
					echo "{$spacer}\t- ".self::ModeProfile."\n";
					echo "{$spacer}\t- ".self::ModeCron."\n";
					echo "\n";
			}
		}

		$this->promptErrors($spacer);
	}
	//
	// Protected methods.
	protected function init() {
		parent::init();

		$dbStructureManager = DBStructureManager::Instance();
		if($dbStructureManager->hasErrors()) {
			foreach($dbStructureManager->errors() as $error) {
				$code = $error[GC_AFIELD_CODE];
				if(is_numeric($code)) {
					$code = sprintf('%03d', $code);
				}

				$this->setError("DB-{$code}", $error[GC_AFIELD_MESSAGE]);
			}
		} else {
			if(!$dbStructureManager->check()) {
				$dbStructureManager->upgrade();
			}
		}
	}
	protected function promptErrors($spacer) {
		foreach($this->errors() as $error) {
			echo "{$spacer}Error: [{$error[GC_AFIELD_CODE]}] {$error[GC_AFIELD_MESSAGE]}.\n";
		}
	}
	protected function runCron($spacer, $params = null) {
		if($this->_tool) {
			$path = $this->paths->shellCron($this->_tool);
			if($path) {
				require_once $path;

				$className = \TooBasic\Names::ShellCronClass($this->_tool);

				if(class_exists($className)) {
					$this->_toolClass = new $className();
					$this->_toolClass->run($spacer, $params);
					$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
				} else {
					$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
				}
			} else {
				$this->setError(self::ErrorUnknownTool, "Unkown cron tool called '{$this->_tool}'");
			}
		} else {
			$this->setError(self::ErrorNoToolName, 'No tool name specified');

			echo "{$spacer}Available crons:\n";
			foreach($this->paths->shellCron('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	protected function runProfile($spacer, $extraParams) {
		global $CronProfiles;

		if($this->_profile) {
			if(isset($CronProfiles[$this->_profile])) {
				echo "{$spacer}Running profile '{$this->_profile}':\n";
				foreach($CronProfiles[$this->_profile] as $tool) {
					$this->_tool = $tool[GC_CRONPROFILES_TOOL];

					$mainParams = array(
						$this->_script,
						self::ModeCron,
						$this->_tool
					);

					$this->runCron("{$spacer}\t", array_merge($mainParams, $tool[GC_CRONPROFILES_PARAMS], $extraParams));
				}
			} else {
				$this->setError(self::ErrorUnknownProfile, "Unkown profile called '{$this->_profile}'");
			}
		} else {
			$this->setError(self::ErrorNoProfileName, 'No profile name specified');

			echo "{$spacer}Available profiles:\n";
			if(count($CronProfiles)) {
				foreach($CronProfiles as $name => $tools) {
					echo "{$spacer}\t- '{$name}' runs:\n";
					foreach($tools as $config) {
						echo "{$spacer}\t\t- {$config[GC_CRONPROFILES_TOOL]}";
						if(count($config[GC_CRONPROFILES_PARAMS])) {
							echo ' ('.implode(' ', $config[GC_CRONPROFILES_PARAMS]).')';
						}
						echo "\n";
					}
				}
			} else {
				echo "{$spacer}\tNo profile available\n";
			}

			echo "\n";
		}
	}
	protected function runSys($spacer, $params = null) {
		if($this->_tool) {
			$path = $this->paths->shellSys($this->_tool);
			if($path) {
				require_once $path;

				$className = \TooBasic\Names::ShellSystoolClass($this->_tool);

				if(class_exists($className)) {
					$this->_toolClass = new $className();
					$this->_toolClass->run($spacer, $params);
					$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
				} else {
					$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
				}
			} else {
				$this->setError(self::ErrorUnknownTool, "Unkown tool called '{$this->_tool}'");
			}
		} else {
			$this->setError(self::ErrorNoToolName, 'No tool name specified');

			echo "{$spacer}Available tools:\n";
			foreach($this->paths->shellSys('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	protected function runTool($spacer, $params = null) {
		if($this->_tool) {
			$path = $this->paths->shellTool($this->_tool);
			if($path) {
				require_once $path;

				$className = \TooBasic\Names::ShellToolClass($this->_tool);

				if(class_exists($className)) {
					$this->_toolClass = new $className();
					$this->_toolClass->run($spacer, $params);
					$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
				} else {
					$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
				}
			} else {
				$this->setError(self::ErrorUnknownTool, "Unkown tool called '{$this->_tool}'");
			}
		} else {
			$this->setError(self::ErrorNoToolName, 'No tool name specified');

			echo "{$spacer}Available tools:\n";
			foreach($this->paths->shellTool('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	protected function setError($code, $message) {
		if(is_numeric($code)) {
			$code = sprintf('%03d', $code);
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);

		$error = array(
			GC_AFIELD_CODE => "SM-{$code}",
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_CLASS => isset($callerLine['class']) ? $callerLine['class'] : false,
			GC_AFIELD_METHOD => $callerLine['function'],
			GC_AFIELD_FILE => $callingLine['file'],
			GC_AFIELD_LINE => $callingLine['line']
		);

		$this->_errors[] = $error;
	}
	protected function starterOptions() {
		$options = Options::Instance();

		$options->addMainOption('script');
		$options->addMainOption('mode');
		$options->addMainOption('tool');

		return $options;
	}
}
