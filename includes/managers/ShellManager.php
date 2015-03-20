<?php

namespace TooBasic;

class ShellManager extends Manager {
	//
	// Protected properties.
	protected $_mode = false;
	protected $_script = false;
	protected $_tool = false;
	//
	// Magic methods.
	//
	// Public methods.
	public function run() {
		$options = $this->starterOptions();
		$options->check();

		$this->_script = $options->script;
		$this->_mode = $options->mode;
		$this->_tool = $options->tool;

		switch($this->_mode) {
			case "tool":
				$this->runTool();
				break;
			case "cron":
				$this->runCron();
				break;
			default:
				/** @todo this should be a little more pretty. */
				if($this->_mode) {
					die("ERROR: No valid mode specified\n");
				} else {
					die("ERROR: No mode specified\n");
				}
		}
	}
	//
	// Protected methods.
	protected function loadCron() {
		$out = false;

		return $out;
	}
	protected function loadTool() {
		$out = false;

		return $out;
	}
	protected function runCron() {
		$tool = $this->loadCron();
		echo "ITS A CRON called: {$this->_tool}\n";
	}
	protected function runTool() {
		$tool = $this->loadTool();

		echo "ITS A TOOL called: {$this->_tool}\n";
	}
	protected function starterOptions() {
		$options = Shell\Options::Instance();

		$options->addMainOption("script");
		$options->addMainOption("mode");
		$options->addMainOption("tool");

		return $options;
	}
}
