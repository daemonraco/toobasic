<?php

namespace TooBasic\Shell;

class Option {
	//
	// Constants.
	const TypeNoValue = "novalue";
	const TypeValue = "value";
	const TypeMultiValue = "multivalue";
	// 
	// Protected properties.
	protected $_activated = false;
	protected $_helpText = false;
	protected $_helpTextFull = false;
	protected $_helpValueName = "value";
	protected $_lastValue = false;
	protected $_name = false;
	protected $_needsMore = false;
	protected $_triggers = array();
	protected $_type = false;
	protected $_values = array();
	//
	// Magic methods.
	public function __construct($name, $type = self::TypeNoValue) {
		$this->_name = $name;
		$this->_type = $type;

		$this->reset();
	}
	//
	// Public methods.
	public function activated() {
		return $this->_activated;
	}
	public function addTrigger($trigger) {
		return $this->_triggers[] = $trigger;
	}
	public function check($param) {
		$matched = false;

		if($this->needsMore()) {
			$this->_values[] = $param;
			$this->_lastValue = $param;
			$this->_needsMore = false;

			$matched = true;
		} else {
			if(in_array($param, $this->_triggers)) {
				static $needingMoreTypes = array(
					self::TypeValue,
					self::TypeMultiValue
				);
				$this->_activated = true;
				if(in_array($this->_type, $needingMoreTypes)) {
					$this->_needsMore = true;
				}

				$matched = true;
			}
		}

		return $matched;
	}
	public function helpText($spacer = "") {
		$out = "";

		if($this->_helpTextFull === false) {
			$subSpacer = "        ";

			$values = "";
			if(in_array($this->_type, array(self::TypeMultiValue, self::TypeValue))) {
				$values = " <{$this->_helpValueName}>";
			}

			foreach($this->_triggers as $trigger) {
				if($out) {
					$out.=", ";
				} else {
					$out = $subSpacer;
				}
				$out.="{$trigger}{$values}";
			}
			$out.="\n";

			if($this->_helpText) {
				$auxText = explode("\n", $this->_helpText);
				foreach($auxText as &$line) {
					$line = "{$subSpacer}{$subSpacer}{$line}";
				}
				$out.= implode("\n", $auxText);
				$out.="\n";
			}
			$out.="\n";

			$this->_helpTextFull = $out;
		} else {
			$out = $this->_helpTextFull;
		}

		return $out;
	}
	public function name() {
		return $this->_name;
	}
	public function needsMore() {
		return $this->_needsMore;
	}
	public function reset() {
		$this->_activated = false;
		$this->_lastValue = false;
		$this->_needsMore = false;
		$this->_values = array();
		$this->_helpText = false;
		$this->_helpTextFull = false;
		$this->_helpValueName = "value";
	}
	public function setHelpText($text, $valueName = "value") {
		$this->_helpValueName = $valueName;
		return $this->_helpText = $text;
	}
	public function stauts() {
		return $this->_name && $this->_triggers;
	}
	public function value() {
		$out = false;

		if($this->activated()) {
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
	public function triggers() {
		return $this->_triggers;
	}
	//
	// Public class methods
	public static function EasyFactory($name, $triggers, $type = self::TypeNoValue, $helpText = false, $helpTextValue = "value") {
		$out = new self($name, $type);

		foreach($triggers as $trigger) {
			$out->addTrigger($trigger);
		}

		if($helpText !== false) {
			$out->setHelpText($helpText, $helpTextValue);
		}

		return $out;
	}
}
