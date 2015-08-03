<?php

/**
 * @file ShellTool.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

/**
 * @class ShellTool
 * @abstract
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
	protected $_coreTasks = array();
	protected $_errors = array();
	/**
	 * @var \TooBasic\Shell\Options
	 */
	protected $_options = false;
	/**
	 * @var string
	 */
	protected $_version = '0.1';
	//
	// Magic methods.
	public function __construct() {
		$this->_coreTasks[] = self::OptionNameHelp;
		$this->_coreTasks[] = self::OptionNameVersion;
		$this->_coreTasks[] = self::OptionNameInfo;

		$this->starterOptions();
		$this->setOptions();
	}
	/**
	 * @todo doc
	 *
	 * @param type $prop @todo doc
	 * @return mixed @todo doc
	 */
	public function __get($prop) {
		$out = false;

		try {
			$out = \TooBasic\MagicProp::Instance()->{$prop};
		} catch(\TooBasic\MagicPropException $ex) {
			
		}

		return $out;
	}
	//
	// Public methods.
	public function errors() {
		return $this->_errors;
	}
	public function hasErrors() {
		return count($this->errors()) > 0;
	}
	public function run($spacer = '', $params = null) {
		if($this->_options->check($params)) {
			$taskName = $this->guessTask();
			//
			// Running the appropiate task.
			$this->{$taskName}($spacer);
		} else {
			$this->setCoreError(self::ErrorWrongParameters, "There's something wrong with your parameters");
			$this->taskHelp($spacer);
		}
	}
	//
	// Protected methods.
	protected function guessTask(&$isCore = false, $avoidCores = false) {
		$taskName = false;
		$isCore = false;

		$activeOptions = $this->_options->activeOptions();

		if($avoidCores) {
			$activeOptions = array_diff($activeOptions, $this->_coreTasks);
		} else {
			$coreActiveOptions = array_intersect($this->_coreTasks, $activeOptions);
			if(count($coreActiveOptions) > 0) {
				//
				// If there's a core option active, the rest doesn't
				// matter.
				$activeOptions = $coreActiveOptions;
				$isCore = true;
			}
		}

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
	abstract protected function taskInfo($spacer = '');
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
