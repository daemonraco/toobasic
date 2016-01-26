<?php

/**
 * @file FormType.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

//
// Class aliases.
use TooBasic\MagicProp;
use TooBasic\MagicPropException;

/**
 * @class FormType
 * @abstract
 * This abstract class represents a type of form builder, it holds all generic
 * method for a HTML form generation.
 */
abstract class FormType {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Forms\Form Form specification shortcut.
	 */
	protected $_form = false;
	/**
	 * @var boolean This flag indicates if current building form is read-only
	 * or not.
	 */
	protected $_readonly = null;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param \TooBasic\Forms\Form $form Form specification object.
	 */
	public function __construct(Form $form) {
		$this->_form = $form;
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
	/**
	 * @abstract
	 * This method build a HTML form code based on its configuration.
	 *
	 * @param mixed[string] $item Information to fill fields (except for mode
	 * 'create').
	 * @param string $mode Mode in which it must be built.
	 * @param mixed[string] $flags List of extra parameters used to build.
	 * @return string Returns a HTML piece of code.
	 * @throws \TooBasic\Forms\FormsException
	 */
	abstract public function buildFor($item, $mode, $flags);
	//
	// Protected methods.
	/**
	 * This method converts a list of parameters into a string that can be
	 * inserted in HTML tag.
	 *
	 * @param \stdClass $attrs List of attributes.
	 * @param boolean $isForm When TRUE it ignores some form attributes
	 * managed by other functionalities.
	 * @return string Returns the same list as a string.
	 */
	protected function attrsToString($attrs, $isForm = false) {
		//
		// Default values.
		$out = '';
		//
		// Appending each attribute.
		$ignoredFormAttrs = array('action', 'id', 'method');
		foreach(get_object_vars($attrs) as $k => $v) {
			//
			// Ignoring core form attributes.
			if($isForm && in_array($k, $ignoredFormAttrs)) {
				continue;
			}
			//
			// Checking if it's an attribute with value or not.
			if($v === true) {
				$out.= " {$k}";
			} else {
				$out.= " {$k}=\"{$v}\"";
			}
		}

		return $out;
	}
	/**
	 * This method expands the list of extra parameters given when a form is
	 * called for building.
	 *
	 * @param mixed[string] $flags List of extra parameters.
	 */
	protected function expandBuildFlags(&$flags) {
		//
		// Forcing the default spacer.
		if(!isset($flags[GC_FORMS_BUILDFLAG_SPACER])) {
			$flags[GC_FORMS_BUILDFLAG_SPACER] = '';
		}
	}
}
