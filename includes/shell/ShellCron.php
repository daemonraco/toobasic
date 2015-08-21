<?php

/**
 * @file ShellCron.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Sanitizer as TB_Sanitizer;
use \TooBasic\Shell\Option as TBS_Option;

/**
 * @class ShellCron
 * @abstract
 */
abstract class ShellCron extends ShellTool {
	//
	// Constants.
	const ErrorRunning = 3;
	const OptionNameClearFlag = 'ClearFlag';
	//
	// Magic methods.
	public function __construct() {
		parent::__construct();

		$this->_coreTasks[] = self::OptionNameClearFlag;
	}
	//
	// Public methods.
	public function run($spacer = '', $params = null) {
		if($this->_options->check($params)) {
			$taskName = $this->guessTask($isCore);
			//
			// Running the appropiate task.
			if($isCore) {
				$this->{$taskName}($spacer);
			} else {
				$this->launchTask($taskName, $spacer);
			}
		} else {
			$this->setCoreError(self::ErrorWrongParameters, "There's something wrong with your parameters");
			$this->taskHelp($spacer);
		}
	}
	//
	// Protected methods.
	protected function flagPath($taskName) {
		global $Directories;
		return TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SHELL_FLAGS]}/".get_called_class()."_{$taskName}.flag");
	}
	protected function launchTask($taskName, $spacer) {
		$flagPath = $this->flagPath($taskName);

		if(is_file($flagPath)) {
			$this->setCoreError(self::ErrorRunning, "There's another instance of this task already running");
		} else {
			file_put_contents($flagPath, '');
			$this->{$taskName}($spacer);
			unlink($flagPath);
		}
	}
	protected function taskClearFlag() {
		$taskName = $this->guessTask($isCore, true);
		$flagPath = $this->flagPath($taskName);

		if(is_file($flagPath)) {
			unlink($flagPath);
		}
	}
	protected function starterOptions() {
		parent::starterOptions();

		$auxOption = new TBS_Option(self::OptionNameClearFlag);
		$auxOption->addTrigger('-CF');
		$auxOption->setHelpText('Updates all tag members counts');
		$this->_options->addOption($auxOption);
	}
	//
	// Private methods.
	private function setCoreError($code, $message) {
		if(is_numeric($code)) {
			$code = sprintf('%03d', $code);
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);

		$error = array(
			GC_AFIELD_CODE => "ST-{$code}",
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_CLASS => isset($callerLine['class']) ? $callerLine['class'] : false,
			GC_AFIELD_METHOD => $callerLine['function'],
			GC_AFIELD_FILE => $callingLine['file'],
			GC_AFIELD_LINE => $callingLine['line']
		);
		$this->_errors[] = $error;
	}
}
