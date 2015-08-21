<?php

/**
 * @file ShellTool.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use \TooBasic\MagicProp;
use \TooBasic\MagicPropException;

/**
 * @class ShellTool
 * @abstract
 * This class is the basic representation of TooBasic's shell tool. In other
 * words, a tool that can be invoked by 'shell.php' from command line.
 */
abstract class ShellTool {
	//
	// Constants.
	const ErrorWrongParameters = 1;
	const ErrorNoTask = 2;
	const OptionNameHelp = 'Help';
	const OptionNameInfo = 'Info';
	const OptionNameVersion = 'Version';
	//
	// Protected properties.
	/**
	 * @var string[] List of options considered core to any tool.
	 */
	protected $_coreTasks = array();
	/**
	 * @var mixed[] List of errors found.
	 */
	protected $_errors = array();
	/**
	 * @var \TooBasic\Shell\Options Options manager shortcut.
	 */
	protected $_options = false;
	/**
	 * @var string Tool's version number.
	 */
	protected $_version = '0.1';
	//
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		//
		// Setting core option names.
		$this->_coreTasks[] = self::OptionNameHelp;
		$this->_coreTasks[] = self::OptionNameVersion;
		$this->_coreTasks[] = self::OptionNameInfo;
		//
		// Starting options settings.
		$this->starterOptions();
		$this->setOptions();
	}
	/**
	 * This magic method provides access to all 'magic properties'.
	 *
	 * @param string $prop Name of the property retrieve.
	 * @return mixed A magic property or FALSE when it's not found.
	 */
	public function __get($prop) {
		//
		// Defualt values.
		$out = false;
		//
		// Looking for the right property.
		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			//
			// Ignoring the error when it's not found.
		}

		return $out;
	}
	//
	// Public methods.
	/**
	 * This method provides access to the list of found errors.
	 *
	 * @return mixed[] Return a list of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method indicates if this tool has registerd errors.
	 *
	 * @return boolean Returns TRUE where there's at least one error
	 * registered.
	 */
	public function hasErrors() {
		return count($this->errors()) > 0;
	}
	/**
	 * This is the main method to start this tool's complete analysis.
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
			//
			// Running the appropiate task.
			$taskName = $this->guessTask();
			$this->{$taskName}($spacer);
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
	 * This method tries to guess the proper method to be executed based on
	 * parameters given through command line.
	 *
	 * @param boolean $isCore
	 * @param boolean $avoidCores
	 * @return string Returns a method name.
	 */
	protected function guessTask(&$isCore = false, $avoidCores = false) {
		//
		// Default values.
		$taskName = false;
		$isCore = false;
		//
		// Obtaining the list of options activated through command line.
		$activeOptions = $this->_options->activeOptions();
		//
		// Checking if core options like '--help' and others have to be
		// ignored.
		if($avoidCores) {
			$activeOptions = array_diff($activeOptions, $this->_coreTasks);
		} else {
			//
			// Checking if there's a core option activated.
			$coreActiveOptions = array_intersect($this->_coreTasks, $activeOptions);
			if(count($coreActiveOptions) > 0) {
				//
				// If there's a core option active, the rest
				// doesn't matter.
				$activeOptions = $coreActiveOptions;
				$isCore = true;
			}
		}
		//
		// Guessing the method based on selected options
		foreach($activeOptions as $optionName) {
			$taskName = "task{$optionName}";
			if(method_exists($this, $taskName)) {
				break;
			} else {
				$taskName = false;
			}
		}
		//
		// If there's no task, main task must be run.
		if(!$taskName) {
			$taskName = 'mainTask';
		}

		return $taskName;
	}
	protected function mainTask($spacer = '') {
		$this->setCoreError(self::ErrorNoTask, 'No task specified');
		$this->taskHelp($spacer);
	}
	protected function setError($code, $message) {
		if(is_numeric($code)) {
			$code = sprintf('%03d', $code);
		}

		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);

		$error = array(
			GC_AFIELD_CODE => "T-{$code}",
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_CLASS => isset($callerLine['class']) ? $callerLine['class'] : false,
			GC_AFIELD_METHOD => $callerLine['function'],
			GC_AFIELD_FILE => $callingLine['file'],
			GC_AFIELD_LINE => $callingLine['line']
		);
		$this->_errors[] = $error;
	}
	abstract protected function setOptions();
	protected function starterOptions() {
		$this->_options = Options::Instance();
		$this->_options->reset();

		$this->_options->addMainOption('ignored_script');
		$this->_options->addMainOption('ignored_mode');
		$this->_options->addMainOption('ignored_tool');

		$auxOption = new Option(self::OptionNameHelp);
		$auxOption->addTrigger('--help');
		$auxOption->addTrigger('-h');
		$auxOption->setHelpText('Shows this help text.');
		$this->_options->addOption($auxOption);

		$auxOption = new Option(self::OptionNameVersion);
		$auxOption->addTrigger('--version');
		$auxOption->addTrigger('-V');
		$auxOption->setHelpText("Shows this tool's version number.");
		$this->_options->addOption($auxOption);

		$auxOption = new Option(self::OptionNameInfo);
		$auxOption->addTrigger('--info');
		$auxOption->addTrigger('-I');
		$auxOption->setHelpText("Shows this tool's information.");
		$this->_options->addOption($auxOption);
	}
	protected function taskHelp($spacer = '') {
		echo $this->_options->helpText();
	}
	protected function taskInfo($spacer = '') {
		$this->taskVersion($spacer);
	}
	protected function taskVersion($spacer = '') {
		echo "{$spacer}Version: {$this->_version}\n";
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
