<?php

/**
 * @file Options.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

/**
 * @class Options
 */
class Options extends \TooBasic\Singleton {
	// 
	// Protected properties.
	protected $_activeOptions = false;
	protected $_helpText = false;
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
	public function activeOptions() {
		if($this->_activeOptions === false) {
			$this->_activeOptions = array();

			foreach($this->_options as $name => $option) {
				if($option->activated()) {
					$this->_activeOptions[] = $name;
				}
			}
		}

		return $this->_activeOptions;
	}
	public function addMainOption($name) {
		$this->_mainOptions[$name] = false;
	}
	public function addOption(Option $option) {
		$this->_options[$option->name()] = $option;
	}
	public function check($params = null) {
		$ok = true;

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
				$lastOption->check($param);
				$lastOption = false;
				$needsMore = false;
			} else {
				$found = false;
				foreach($this->_options as &$option) {
					if($option->check($param)) {
						if($option->needsMore()) {
							$lastOption = $option;
							$needsMore = true;
						}

						$found = true;
						break;
					}
				}
				if(!$found) {
					$this->_unknownParams[] = $param;
				}
			}
		}

		if($needsMore) {
			$ok = false;
		}

		return $ok;
	}
	public function helpText($spacer = '') {
		$out = '';

		if($this->_helpText) {
			$out.="{$this->_helpText}\n\n";
		}

		foreach($this->_options as $option) {
			$out.=$option->helpText($spacer);
		}

		$out = explode("\n", $out);
		foreach($out as &$line) {
			$line = "{$spacer}{$line}";
		}
		$out = implode("\n", $out);
		$out.= "\n";

		return $out;
	}
	public function option($name) {
		return isset($this->_options[$name]) ? $this->_options[$name] : null;
	}
	public function reset() {
		$this->_activeOptions = false;
		$this->_mainOptions = array();
		$this->_unknownParams = array();

		foreach($this->_options as $option) {
			$option->reset();
		}
	}
	public function setHelpText($text) {
		return $this->_helpText = $text;
	}
	public function unknownParams() {
		return $this->_unknownParams;
	}
}
