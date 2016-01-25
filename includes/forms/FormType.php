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
use TooBasic\Managers\RoutesManager;

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
	 * @var \stdClass Current builder configuration shortcut.
	 */
	protected $_config = false;
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
	 * @param \stdClass $config Form configuration to work with.
	 */
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
	 * This method returns the proper form action based on its defaults and
	 * specific values for certain mode.
	 *
	 * @param string $mode Mode to be used when checking action.
	 * @return string Returns a URL.
	 */
	protected function action($mode) {
		//
		// Default value.
		$action = $this->_config->form->action;
		//
		// Current mode's value.
		if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->action)) {
			$action = $this->_config->form->modes->{$mode}->action;
		}
		//
		// Cleaning routes and returning.
		return RoutesManager::Instance()->enroute($action);
	}
	/**
	 * This method returns a proper list of form's HTML attributes merging
	 * defaults and specific attributes for certain mode.
	 *
	 * @param string $mode Mode to be used when merging attributes.
	 * @return \stdClass Returns a complete list of attributes.
	 */
	protected function attrs($mode) {
		//
		// Default values.
		$out = new \stdClass();
		//
		// Copying default values.
		foreach($this->_config->form->attrs as $k => $v) {
			$out->{$k} = $v;
		}
		//
		// Copying mode's form attributies, if any.
		if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->attrs)) {
			foreach($this->_config->form->modes->{$mode}->attrs as $k => $v) {
				$out->{$k} = $v;
			}
		}

		return $out;
	}
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
	 * This method returns the proper list of buttons based on current form
	 * defaults and specific buttons for certain mode.
	 *
	 * @param string $mode Mode to be used when checking buttons.
	 * @return \stdClass Returns a list of buttons.
	 */
	protected function buttonsFor($mode) {
		//
		// Default values.
		$out = $this->_config->form->buttons;
		//
		// Checking mode's buttons.
		if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->buttons)) {
			$out = $this->_config->form->modes->{$mode}->buttons;
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
	/**
	 * This method allows to know if current form is in read-only mode or not.
	 *
	 * @param string $mode Mode to be used when checking read-only status.
	 * @return boolean Returns TRUE when it's a read-only form.
	 */
	protected function isReadOnly($mode) {
		if(is_null($this->_readonly)) {
			$this->_readonly = $this->_config->form->readonly || $mode == GC_FORMS_BUILDMODE_VIEW || $mode == GC_FORMS_BUILDMODE_REMOVE;

			if(!$this->_readonly && isset($this->_config->form->{$mode}) && isset($this->_config->form->{$mode}->readonly)) {
				$this->_readonly = $this->_config->form->{$mode}->readonly;
			}
		}
		return $this->_readonly;
	}
	/**
	 * This method returns the proper form method based on its defaults and
	 * specific values for certain mode.
	 *
	 * @param string $mode Mode to be used when checking mode.
	 * @return string Returns a method name.
	 */
	protected function method($mode) {
		return isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->method) ? $this->_config->form->modes->{$mode}->method : $this->_config->form->method;
	}
}
