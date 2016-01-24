<?php

/**
 * @file Form.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class FormWriter
 * @todo doc
 */
class FormWriter {
	//
	// Protected properties.
	/**
	 * @var \stdClass Configuration shortcut.
	 */
	protected $_config = false;
	/**
	 * @var boolean This flag indicate that current configuration has been
	 * changed.
	 */
	protected $_dirty = false;
	/**
	 * @var \TooBasic\Forms\Form Form to modify shortcut.
	 */
	protected $_form = false;
	/**
	 * @var string Form's path shortcut.
	 */
	protected $_path = false;
	//
	// Magic methods.
	public function __construct(Form $form) {
		//
		// Shortcut.
		$this->_form = $form;
		//
		// Checking form.
		if(!$this->_form->path()) {
			throw new FormsException('Given form has no specification.');
		} else {
			$this->_path = $this->_form->path();
		}
		//
		// Loading configuration.
		$this->loadConfig();
	}
	//
	// Public methods.
	public function addField($name, $type, &$error = false) {
		$ok = true;
		//
		// Checking type
		$typeValues = explode(':', $type);
		$type = array_shift($typeValues);
		if(!in_array($type, array('input', 'password', 'text', 'enum'))) {
			$error = "Unknown type '{$type}'";
			$ok = false;
		}
		if($type == 'enum' && !count($typeValues)) {
			$error = "Enumerative type requires values";
			$ok = false;
		}
		//
		// Adding field
		if($ok) {
			if(!isset($this->_config->form->fields->{$name})) {
				$this->_config->form->fields->{$name} = new \stdClass();
				$this->_config->form->fields->{$name}->type = $type;
				$this->_dirty = true;
			} else {
				$error = "Field '{$name}' already defined";
				$ok = false;
			}
		}
		debugit('TODO', 0);

		return $ok;
	}
	public function dirty() {
		return $this->_dirty;
	}
	public function setAction($action, $mode = false) {
		$this->setMainValue('action', $action, $mode);
	}
	public function setFieldDefault($name, $value) {
		debugit('TODO', 0);
	}
	public function setMethod($method, $mode = false) {
		$this->setMainValue('method', $method, $mode);
	}
	public function setName($name, $mode = false) {
		$this->setMainValue('name', $name, $mode);
	}
	public function save() {
		return $this->dirty() ? \boolval(file_put_contents($this->_path, json_encode($this->_config, JSON_PRETTY_PRINT))) : false;
	}
	//
	// Protected methods.
	/**
	 * @todo doc
	 *
	 * @throws \TooBasic\Forms\FormsException
	 */
	protected function loadConfig() {
		//
		// Loading configuration file.
		$this->_config = json_decode(file_get_contents($this->_path));
		//
		// Checking configuration.
		if(!$this->_config) {
			throw new FormsException("Unable to load configuration at '{$this->_path}'.");
		}
		//
		// Expanding basic fields.
		if(!isset($this->_config->form)) {
			$this->_config->form = new \stdClass();
			$this->_dirty = true;
		}
		if(!isset($this->_config->form->fields)) {
			$this->_config->form->fields = new \stdClass();
			$this->_dirty = true;
		}
	}
	public function setMainValue($name, $value, $mode = false) {
		if($mode) {
			if(!isset($this->_config->form->modes)) {
				$this->_config->form->modes = new \stdClass();
				$this->_dirty = true;
			}
			if(!isset($this->_config->form->modes->{$mode})) {
				$this->_config->form->modes->{$mode} = new \stdClass();
				$this->_dirty = true;
			}
			if(!isset($this->_config->form->modes->{$mode}->{$name}) || $this->_config->form->modes->{$mode}->{$name} != $value) {
				$this->_config->form->modes->{$mode}->{$name} = $value;
				$this->_dirty = true;
			}
		} else {
			if(!isset($this->_config->form->{$name}) || $this->_config->form->{$name} != $value) {
				$this->_config->form->{$name} = $value;
				$this->_dirty = true;
			}
		}
	}
}
