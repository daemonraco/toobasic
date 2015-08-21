<?php

/**
 * @file ShellCron.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Sanitizer;
use \TooBasic\Shell\Option;

/**
 * @class ShellCron
 * @abstract
 * This class is an abstrac specfication of a shell tool that can be executed
 * periodically by a cron mechanism.
 * It's main differance with a simple shell tool is that it avoids simultaneous
 * executions of the same tool (and method).
 */
abstract class ShellCron extends ShellTool {
	//
	// Constants.
	const ErrorRunning = 3;
	const OptionNameClearFlag = 'ClearFlag';
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Adding a new core option.
		$this->_coreTasks[] = self::OptionNameClearFlag;
	}
	//
	// Public methods.
	/**
	 * This is the main method to start this cron tool's complete analysis.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @param string[] $params List of parameters given in command line
	 * required to analyze options.
	 */
	public function run($spacer = '', $params = null) {
		//
		// Reading and checking command line arguments.
		if($this->_options->check($params)) {
			$isCore = false;
			//
			// Running the appropiate task.
			$taskName = $this->guessTask($isCore);
			if($isCore) {
				//
				// Core options run directly because they don't
				// need any simultaneous execution check.
				$this->{$taskName}($spacer);
			} else {
				$this->launchTask($taskName, $spacer);
			}
		} else {
			//
			// If there's an error on the command line parametrs, it's
			// informed and a help text is shown.
			$this->setCoreError(self::ErrorWrongParameters, "There's something wrong with your parameters");
			$this->taskHelp($spacer);
		}
	}
	//
	// Protected methods.
	/**
	 * This methods generates an appropriate flag file path for certain
	 * method.
	 *
	 * @param string $taskName Name of the method associated with the flag.
	 * @return string Returns and absolute file path.
	 */
	protected function flagPath($taskName) {
		global $Directories;
		return Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SHELL_FLAGS]}/".get_called_class()."_{$taskName}.flag");
	}
	/**
	 * This method is the one in charge of firing up the required task
	 * checking for flags before doing so.
	 *
	 * @param string $taskName Name of the method to run.
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 */
	protected function launchTask($taskName, $spacer) {
		//
		// Obtaining the write flag file path.
		$flagPath = $this->flagPath($taskName);
		//
		// Checking for flags of another execution instance.
		if(is_file($flagPath)) {
			$this->setCoreError(self::ErrorRunning, "There's another instance of this task already running");
		} else {
			//
			// Creating a flag file.
			file_put_contents($flagPath, '');
			//
			// Running the requested method.
			$this->{$taskName}($spacer);
			//
			// Clearing the flag.
			unlink($flagPath);
		}
	}
	/**
	 * This method attends the core option that clears a dead flag.
	 * Dead flags appear when an execution stops unexpectedly due to some
	 * unhandled error.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 */
	protected function taskClearFlag($spacer = '') {
		$isCore = true;
		//
		// Guessing the right method.
		$taskName = $this->guessTask($isCore, true);
		//
		// Guessing the right flag file.
		$flagPath = $this->flagPath($taskName);
		//
		// If it's present it gets removed.
		if(is_file($flagPath)) {
			unlink($flagPath);
		}
	}
	/**
	 * This method sets options required by cron tools. Basically, it adds the
	 * core option for clearing dead flags.
	 */
	protected function starterOptions() {
		parent::starterOptions();

		$auxOption = new Option(self::OptionNameClearFlag);
		$auxOption->addTrigger('-CF');
		$auxOption->setHelpText('Updates all tag members counts');
		$this->_options->addOption($auxOption);
	}
	//
	// Private methods.
	/**
	 * This method adds an error in it's interal list and considers it as a
	 * core error.
	 *
	 * @param int $code Code to be associated with this error.
	 * @param string $message Text to be associated with this error.
	 */
	private function setCoreError($code, $message) {
		$this->setError($code, $message, 'ST', true);
	}
}
