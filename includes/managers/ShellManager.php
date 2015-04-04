<?php

namespace TooBasic;

class ShellManager extends Manager {
	//
	// Protected properties.
	protected $_mode = false;
	protected $_script = false;
	protected $_tool = false;
	protected $_toolClass = null;
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

		if($this->_tool) {
			debugit("ITS A CRON called: {$this->_tool}");
		} else {
			die("ERROR: No tool name specified\n");
		}
	}
	protected function runTool() {
		$tool = $this->loadTool();

		if($this->_tool) {
			$path = Paths::Instance()->shellTool($this->_tool);
			if($path) {
				require_once $path;

				$className = classname($this->_tool)."Tool";

				if(class_exists($className)) {
					$this->_toolClass = new $className ();
					$this->_toolClass->run();
				} else {
					die("ERROR: Class '{$className}' doesn't exist.\n");
				}
			} else {
				die("ERROR: Unkown tool called {$this->_tool}\n");
			}
		} else {
			die("ERROR: No tool name specified\n");
		}
	}
	protected function starterOptions() {
		$options = Shell\Options::Instance();

		$options->addMainOption("script");
		$options->addMainOption("mode");
		$options->addMainOption("tool");

		return $options;
	}
}
