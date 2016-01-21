<?php

namespace TooBasic\Forms;

use TooBasic\MagicProp;
use TooBasic\MagicPropException;

abstract class FormType {
	//
	// Protected properties.
	protected $_config;
	//
	// Magic methods.
	public function __construct($config) {
		$this->_config = $config;
	}
	/**
	 * This magic method provides a shortcut for magicprops
	 *
	 * @param string $prop Name of the magic property to look for.
	 * @return mixed Returns the requested magic property or FALSE if it was
	 * not found.
	 */
	public function __get($prop) {
		//
		// Default values.
		$out = false;
		//
		// Looking for the requested property.
		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			//
			// Ignored to avoid unnecessary issues.
		}

		return $out;
	}
	//
	// Public methods.
	abstract public function buildFor($item, $mode, $flags);
	//
	// Protected methods.
	protected function attrs($mode) {
		$out = new \stdClass();

		foreach($this->_config->form->attrs as $k => $v) {
			$out->{$k} = $v;
		}

		if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->attrs)) {
			foreach($this->_config->form->modes->{$mode}->attrs as $k => $v) {
				$out->{$k} = $v;
			}
		}

		return $out;
	}
	protected function action($mode) {
		return isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->action) ? $this->_config->form->modes->{$mode}->action : $this->_config->form->action;
	}
	protected function attrsToString($attrs) {
		$out = '';

		foreach(get_object_vars($attrs) as $k => $v) {
			if($v === true) {
				$out.= " {$k}";
			} else {
				$out.= " {$k}=\"{$v}\"";
			}
		}

		return $out;
	}
	protected function buttonsFor($mode) {
		$out = $this->_config->form->buttons;

		if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->buttons)) {
			$out = $this->_config->form->modes->{$mode}->buttons;
		}

		return $out;
	}
	protected function expandBuildFlags(&$flags) {
		if(!isset($flags[GC_FORMS_BUILDFLAG_SPACER])) {
			$flags[GC_FORMS_BUILDFLAG_SPACER] = '';
		}
	}
	protected function isReadOnly($mode) {
		return $mode == GC_FORMS_BUILDMODE_VIEW || $mode == GC_FORMS_BUILDMODE_REMOVE;
	}
	protected function method($mode) {
		return isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->method) ? $this->_config->form->modes->{$mode}->method : $this->_config->form->method;
	}
}
