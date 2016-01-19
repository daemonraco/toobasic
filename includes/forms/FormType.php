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
	protected function expandBuildFlags(&$flags) {
		if(!isset($flags[GC_FORMS_BUILDFLAG_SPACER])) {
			$flags[GC_FORMS_BUILDFLAG_SPACER] = '';
		}
		if(!isset($flags[GC_FORMS_BUILDFLAG_ACTION])) {
			$flags[GC_FORMS_BUILDFLAG_ACTION] = '#';
		}
		if(!isset($flags[GC_FORMS_BUILDFLAG_METHOD])) {
			$flags[GC_FORMS_BUILDFLAG_METHOD] = isset($this->_config->method) ? $this->_config->method : 'get';
		}
		if(!isset($flags[GC_FORMS_BUILDFLAG_ONSUBMIT])) {
			$flags[GC_FORMS_BUILDFLAG_ONSUBMIT] = isset($this->_config->onSubmit) ? $this->_config->onSubmit : false;
		}
	}
	protected function isReadOnly($mode) {
		return $mode == GC_FORMS_BUILDMODE_VIEW || $mode == GC_FORMS_BUILDMODE_REMOVE;
	}
}
