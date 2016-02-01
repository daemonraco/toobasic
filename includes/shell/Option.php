<?php

/**
 * @file Option.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

/**
 * @class Option
 * This class represents a single option given on a command line and provides
 * management and checks over it.
 */
class Option {
	//
	// Constants.
	const TypeNoValue = 'novalue';
	const TypeValue = 'value';
	const TypeMultiValue = 'multivalue';
	// 
	// Protected properties.
	/**
	 * @var boolean This flag indicates that this options has been used in
	 * command line.
	 */
	protected $_activated = false;
	/**
	 * @var string This text explain how this option can be useful.
	 */
	protected $_helpText = false;
	/**
	 * @var string Same as '$_helpText' but formatted.
	 */
	protected $_helpTextFull = false;
	/**
	 * @var string Label to show on a help text for options that support
	 * values.
	 */
	protected $_helpValueName = 'value';
	/**
	 * @var string Last value given to this option on a command line. Useful
	 * for options that only allow one value.
	 */
	protected $_lastValue = false;
	/**
	 * @var string Identifying name assign to this option.
	 */
	protected $_name = false;
	/**
	 * @var boolean This flag indicates if this option is expecting to recieve
	 * another value to analize.
	 */
	protected $_needsMore = false;
	/**
	 * @var string[] List of values that can activate this option.
	 */
	protected $_triggers = array();
	/**
	 * @var string Indicates the way this option works.
	 */
	protected $_type = false;
	/**
	 * @var string[] List of values given to this option on a command line.
	 * Useful for options that allow more than one value.
	 */
	protected $_values = array();
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $name Name to assign and identify this option.
	 * @param string $type Mechanism to be used by this option.
	 */
	public function __construct($name, $type = self::TypeNoValue) {
		$this->_name = $name;
		$this->_type = $type;
		//
		// Enforcing a proper initialization.
		$this->reset();
	}
	//
	// Public methods.
	/**
	 * This method allows to know if this option has ven given in the command
	 * line.
	 *
	 * @return boolean Returns TRUE when this option has been activated.
	 */
	public function activated() {
		return $this->_activated;
	}
	/**
	 * This method adds possible values that can activate this option when
	 * given in command line.
	 *
	 * @param string $trigger Trigger to be added.
	 * @return string[] Returns the list of triggers.
	 */
	public function addTrigger($trigger) {
		return $this->_triggers[] = $trigger;
	}
	/**
	 * This method allows to check a single value from command line
	 *
	 * @param string $param Value to be analysed.
	 * @return boolean Returns TRUE the given value was accepted and used.
	 */
	public function check($param) {
		//
		// Default values.
		$matched = false;
		//
		// Checking if this option is expecting to recieve a second value.
		if($this->needsMore()) {
			//
			// Saving the value
			$this->_values[] = $param;
			$this->_lastValue = $param;
			//
			// This option is no longer specting for a second value.
			$this->_needsMore = false;
			//
			// At this point, the value is considered to be taken.
			$matched = true;
		} else {
			//
			// Checking if the value is a trigger or not.
			if(in_array($param, $this->_triggers)) {
				//
				// Internal list of options that require values.
				static $needingMoreTypes = array(
					self::TypeValue,
					self::TypeMultiValue
				);
				//
				// Setting this option as activated.
				$this->_activated = true;
				//
				// Checking if it requires a second value.
				if(in_array($this->_type, $needingMoreTypes)) {
					$this->_needsMore = true;
				}
				//
				// At this point, the value is considered to be
				// taken.
				$matched = true;
			}
		}

		return $matched;
	}
	/**
	 * This method generates and returns a properly formatted text to be shown
	 * as help on a shell interface.
	 *
	 * @param string $spacer Text to be used as prefix for each line.
	 * @return string Returns a formatted help text.
	 */
	public function helpText($spacer = '') {
		//
		// Default values.
		$out = '';
		//
		// Checking if this text was already generated.
		if($this->_helpTextFull === false) {
			//
			// Internal spacer.
			$subSpacer = '        ';
			//
			// Piece of text to expess how values are specified.
			$values = '';
			if(in_array($this->_type, array(self::TypeMultiValue, self::TypeValue))) {
				$values = " <{$this->_helpValueName}>";
			}
			//
			// Generating the line with the list triggers.
			foreach($this->_triggers as $trigger) {
				if($out) {
					$out.=', ';
				} else {
					$out = $subSpacer;
				}
				$out.="{$trigger}{$values}";
			}
			$out.="\n";
			//
			// If there's a given help text, it is split, formatted
			// and added.
			if($this->_helpText) {
				$auxText = explode("\n", $this->_helpText);
				foreach($auxText as &$line) {
					$line = "{$subSpacer}{$subSpacer}{$line}";
				}
				$out.= implode("\n", $auxText);
				$out.="\n";
			}
			$out.="\n";
			//
			// Saving the generated text to improve further uses.
			$this->_helpTextFull = $out;
		} else {
			//
			// Using a previous generation.
			$out = $this->_helpTextFull;
		}

		return $out;
	}
	/**
	 * This method provides access to this option's name.
	 *
	 * @return string Returns a name.
	 */
	public function name() {
		return $this->_name;
	}
	/**
	 * This method indicates if this option is expecting for more options.
	 *
	 * @return boolean Returns TRUE when a second value is spected.
	 */
	public function needsMore() {
		return $this->_needsMore;
	}
	/**
	 * This method resets all internal values preparing this option for reuse.
	 */
	public function reset() {
		$this->_activated = false;
		$this->_lastValue = false;
		$this->_needsMore = false;
		$this->_values = array();
	}
	/**
	 * This method allows to set a help text for this option that explains its
	 * functionality.
	 *
	 * @param string $text Help text to be set.
	 * @param string $valueName Piece of text to expess how values are
	 * specified.
	 * @return string Returns the the set text.
	 */
	public function setHelpText($text, $valueName = 'value') {
		$this->_helpValueName = $valueName;
		return $this->_helpText = $text;
	}
	/**
	 * This method allows to know if this option is ready to be used, which
	 * means it has a name assigned and at least a trigger.
	 *
	 * @return boolean Returns TRUE when it's okey to use it.
	 */
	public function stauts() {
		return $this->_name && $this->_triggers;
	}
	/**
	 * This method provides access to values given for this option.
	 *
	 * @return mixed When activated, this options returns TRUE if it doesn't
	 * use second values, the last value recieve when it accepts only one, or
	 * a list of values when it accepts more than one. It always returns FALSE
	 * when it's not activated.
	 */
	public function value() {
		//
		// Default values.
		$out = false;
		//
		// Checking if it's activated.
		if($this->activated()) {
			//
			// Choosing the proper value to be returned.
			switch($this->_type) {
				case self::TypeNoValue:
					$out = true;
					break;
				case self::TypeValue:
					$out = $this->_lastValue;
					break;
				case self::TypeMultiValue:
					$out = $this->_values;
					break;
			}
		}

		return $out;
	}
	/**
	 * This method provides access to the list of triggers for this option.
	 *
	 * @return string[] Returns a list of possible values.
	 */
	public function triggers() {
		return $this->_triggers;
	}
	/**
	 * This method provides access to the current option's type name.
	 *
	 * @return string Returns a type name.
	 */
	public function type() {
		return $this->_type;
	}
	//
	// Public class methods.
	/**
	 * This is a factory method that provides an easy way to build new option
	 * objects.
	 *
	 * @param string $name Name to assign to the new option object.
	 * @param string[] $triggers List of triggers that can activate it.
	 * @param string $type Mechanism to be used.
	 * @param string $helpText Help text to be set.
	 * @param string $helpTextValue Piece of text to expess how values are
	 * specified.
	 * @return \TooBasic\Shell\Option Returns a new object already set.
	 */
	public static function EasyFactory($name, $triggers, $type = self::TypeNoValue, $helpText = false, $helpTextValue = 'value') {
		//
		// Building a new option object.
		$out = new self($name, $type);
		//
		// Assigning triggers.
		foreach($triggers as $trigger) {
			$out->addTrigger($trigger);
		}
		//
		// Setting it's help text.
		if($helpText !== false) {
			$out->setHelpText($helpText, $helpTextValue);
		}

		return $out;
	}
}
