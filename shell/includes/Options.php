<?php

namespace TooBasic\Shell;

class Options extends \TooBasic\Singleton {
	//
	// Constants.
	// 
	// Protected properties.
	protected $_mainOptions = array();
	protected $_options = array();
	protected $_unknownParams = array();
	//
	// Magic methods.
	public function __get($name) {
		return isset($this->_mainOptions[$name]) ? $this->_mainOptions[$name] : null;
	}
	//
	// Public methods.
	public function addMainOption($name) {
		$this->_mainOptions[$name] = false;
	}
	public function addOption(Option $option) {
		$this->_options[$option->name()] = $option;
	}
	public function check($params = null) {
		if($params === null) {
			global $argv;
			$params = $argv;
		}

		foreach($this->_mainOptions as &$option) {
			$option = array_shift($params);
		}

		$needsMore = false;
		$lastOption = false;
		foreach($params as $param) {
			if($needsMore) {
				$option->check($param);
				$lastOption = false;
			} else {
				foreach($this->_options as &$option) {
					if($option->check($param)) {
						$lastOption = $option;
						$needsMore = true;
						break;
					}
				}
			}
		}
//		debugit("------", true);
	}
	public function reset() {
		$this->_mainOptions = array();
		$this->_unknownParams = array();

		foreach($this->_options as $option) {
			$option->reset();
		}
	}
}
