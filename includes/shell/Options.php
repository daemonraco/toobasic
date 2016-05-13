<?php

/**
 * @file Options.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Shell\Option;

/**
 * @class Options
 * This class has the ability to analyse values from a command line running then
 * through a list of possible options.
 */
class Options extends \TooBasic\Singleton {
	// 
	// Protected properties.
	/**
	 * @var string[] This the list of option names that has been activated.
	 */
	protected $_activeOptions = false;
	/**
	 * @var string Full formated help text.
	 */
	protected $_helpText = false;
	/**
	 * @var string[] List of parameters that are always present at the
	 * begining of a command line.
	 */
	protected $_mainOptions = array();
	/**
	 * @var \TooBasic\Shell\Option[] List of options to check when analysing a
	 * command line list of values.
	 */
	protected $_options = array();
	/**
	 * @var string[] List of values that couldn't be associated with any knwon
	 * option.
	 */
	protected $_unknownParams = array();
	//
	// Magic methods.
	/**
	 * This magic method provides a quick access to values associated with
	 * main options.
	 *
	 * @param string $name Main option name to look for.
	 * @return string Returns the given value of NULL when it was not given on
	 * a command line.
	 */
	public function __get($name) {
		return isset($this->_mainOptions[$name]) ? $this->_mainOptions[$name] : null;
	}
	//
	// Public methods.
	/**
	 * This method provides access to a list of names for options that has
	 * been activated.
	 *
	 * @return string[] Returns a list of activated options.
	 */
	public function activeOptions() {
		//
		// Avoiding multiple checks.
		if($this->_activeOptions === false) {
			//
			// Default values.
			$this->_activeOptions = array();
			//
			// Checking each known option.
			foreach($this->_options as $name => $option) {
				//
				// If it's activated it's added to the list.
				if($option->activated()) {
					$this->_activeOptions[] = $name;
				}
			}
		}

		return $this->_activeOptions;
	}
	/**
	 * This method adds a main option to the quere.
	 *
	 * @param string $name Name to associate with it.
	 */
	public function addMainOption($name) {
		$this->_mainOptions[$name] = false;
	}
	/**
	 * This method adds a simple option to the list.
	 *
	 * @param \TooBasic\Shell\Option $option Option to be added.
	 */
	public function addOption(Option $option) {
		$this->_options[$option->name()] = $option;
	}
	/**
	 * This method takes a list of argument received from command line and
	 * analyze them against all known options.
	 *
	 * @param string[] $params List of parameters to analyze. If it's not
	 * present or NULL, super global '$argv' is used.
	 * @return boolean Returns TRUE if there were no errors while checking
	 * given parameters.
	 */
	public function check($params = null) {
		//
		// Default values.
		$ok = true;
		$needsMore = false;
		$lastOption = false;
		//
		// Using super global '$argv' if no parameters list given.
		if($params === null) {
			global $argv;
			$params = $argv;
		}
		//
		// Absorving main options.
		foreach($this->_mainOptions as &$option) {
			$option = array_shift($params);
		}
		//
		// Checking each given parameter.
		foreach($params as $param) {
			//
			// When '$needsMore' is TRUE, it means a previously
			// analyzed option is expecting to consume another
			// parameter.
			if($needsMore) {
				//
				// Sending the value to the last analyzed option.
				$lastOption->check($param);
				$lastOption = false;
				$needsMore = false;
			} else {
				$found = false;
				//
				// Asking each knwon option it current parameter
				// is useful for it.
				foreach($this->_options as &$option) {
					//
					// Asking...
					if($option->check($param)) {
						//
						// Checking if the option requires
						// another value.
						if($option->needsMore()) {
							$lastOption = $option;
							$needsMore = true;
						}

						$found = true;
						break;
					}
				}
				//
				// If no option took the value, it is added to
				// unkwnon parameters list.
				if(!$found) {
					$this->_unknownParams[] = $param;
				}
			}
		}
		//
		// At this point, no option should be waitting for more value,
		// otherwise it would be a bad especification of parameters.
		if($needsMore) {
			$ok = false;
		}

		return $ok;
	}
	/**
	 * This method generates a full help text, also including each option
	 * formated help text.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return string Returns a well formated help text.
	 */
	public function helpText($spacer = '') {
		//
		// Default values.
		$out = '';
		//
		// Adding a general help text.
		if($this->_helpText) {
			$out.="{$this->_helpText}\n\n";
		}
		//
		// Adding each option help text.
		foreach($this->_options as $option) {
			$out.=$option->helpText($spacer);
		}
		//
		// Adding the requested prefix on each line.
		$out = explode("\n", $out);
		foreach($out as &$line) {
			$line = "{$spacer}{$line}";
		}
		$out = implode("\n", $out);
		$out.= "\n";

		return $out;
	}
	/**
	 * This method allows access to certain option based on it name.
	 *
	 * @param string $name Option to look for.
	 * @return \TooBasic\Shell\Option Retruns an option associated with the
	 * requested name or NULL if it's not found.
	 */
	public function option($name) {
		return isset($this->_options[$name]) ? $this->_options[$name] : null;
	}
	/**
	 * This method reset this object and its option to a state where it can be
	 * reused, this means re-checked.
	 */
	public function reset() {
		//
		// Resetting basic values.
		$this->_activeOptions = false;
		$this->_mainOptions = array();
		$this->_unknownParams = array();
		//
		// Resetting options.
		foreach($this->_options as $option) {
			$option->reset();
		}
	}
	/**
	 * Sets a genertic help text.
	 *
	 * @param string $text Text to be set as generic help text.
	 * @return string Retruns the text set.
	 */
	public function setHelpText($text) {
		return $this->_helpText = $text;
	}
	/**
	 * This method provides access to those values that where not used on any
	 * option while analysing a command line.
	 *
	 * @return string[] Returns a list of values.
	 */
	public function unknownParams() {
		return $this->_unknownParams;
	}
}
