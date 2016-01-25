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
	public function addButton($name, $type, $mode = false, &$error = false) {
		$ok = true;
		//
		// Checking type.
		if(!in_array($type, array(GC_FORMS_BOTTONTYPE_BUTTON, GC_FORMS_BOTTONTYPE_RESET, GC_FORMS_BOTTONTYPE_SUBMIT))) {
			$error = "Unknown type '{$type}'";
			$ok = false;
		}
		//
		// Choosing the right object.
		$obj = false;
		if($ok) {
			if($mode) {
				if(!isset($this->_config->form->modes)) {
					$this->_config->form->modes = new \stdClass();
				}
				if(!isset($this->_config->form->modes->{$mode})) {
					$this->_config->form->modes->{$mode} = new \stdClass();
				}

				$obj = $this->_config->form->modes->{$mode};
			} else {
				$obj = $this->_config->form;
			}
		}
		//
		// Adding button.
		if($ok) {
			if(!isset($obj->buttons->{$name})) {
				if(!isset($obj->buttons)) {
					$obj->buttons = new \stdClass();
				}

				$obj->buttons->{$name} = new \stdClass();
				$obj->buttons->{$name}->type = $type;
				$this->_dirty = true;
			} else {
				$error = "Button '{$name}' already defined";
				$ok = false;
			}
		}

		return $ok;
	}
	public function addField($name, $type, &$error = false) {
		$ok = true;
		//
		// Checking type.
		$typeValues = explode(':', $type);
		$type = array_shift($typeValues);
		if(!in_array($type, array(GC_FORMS_FIELDTYPE_HIDDEN, GC_FORMS_FIELDTYPE_INPUT, GC_FORMS_FIELDTYPE_PASSWORD, GC_FORMS_FIELDTYPE_TEXT, GC_FORMS_FIELDTYPE_ENUM))) {
			$error = "Unknown type '{$type}'";
			$ok = false;
		}
		if($type == 'enum' && !count($typeValues)) {
			$error = "Enumerative type requires values";
			$ok = false;
		}
		//
		// Adding field.
		if($ok) {
			if(!isset($this->_config->form->fields->{$name})) {
				$this->_config->form->fields->{$name} = new \stdClass();
				$this->_config->form->fields->{$name}->type = $type;
				if($type == GC_FORMS_FIELDTYPE_ENUM) {
					$this->_config->form->fields->{$name}->values = $typeValues;
				}
				$this->_dirty = true;
			} else {
				$error = "Field '{$name}' already defined";
				$ok = false;
			}
		}

		return $ok;
	}
	public function dirty() {
		return $this->_dirty;
	}
	public function excludeFieldFrom($fieldName, $modes, &$error = false) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			if(!is_array($modes)) {
				$modes = array();
			}

			$this->_config->form->fields->{$fieldName}->excludedModes = $modes;
		} else {
			$error = "Field '{$fieldName}' doesn't exist";
		}
	}
	public function setAction($action, $mode = false) {
		$this->setMainValue('action', $action, $mode);
	}
	public function setAttribute($name, $value, $mode = false) {
		//
		// Choosing the right object.
		$obj = false;
		if($mode) {
			if(!isset($this->_config->form->modes)) {
				$this->_config->form->modes = new \stdClass();
			}
			if(!isset($this->_config->form->modes->{$mode})) {
				$this->_config->form->modes->{$mode} = new \stdClass();
			}

			$obj = $this->_config->form->modes->{$mode};
		} else {
			if(isset($this->_config->form)) {
				$obj = $this->_config->form;
			}
		}
		if($obj) {
			$this->setAttributeTo($name, $value, $obj);
		}
	}
	public function setButtonAttribute($buttonName, $attrName, $value, $mode = false) {
		//
		// Choosing the right object.
		$obj = false;
		if($mode) {
			if(isset($this->_config->form->modes->{$mode}->buttons->{$buttonName})) {
				$obj = $this->_config->form->modes->{$mode}->buttons->{$buttonName};
			}
		} else {
			if(isset($this->_config->form->buttons->{$buttonName})) {
				$obj = $this->_config->form->buttons->{$buttonName};
			}
		}
		if($obj) {
			$this->setAttributeTo($attrName, $value, $obj);
		}
	}
	public function setButtonLabel($buttonName, $value, $mode = false) {
		//
		// Choosing the right object.
		$obj = false;
		if($mode) {
			if(isset($this->_config->form->modes->{$mode}->buttons->{$buttonName})) {
				$obj = $this->_config->form->modes->{$mode}->buttons->{$buttonName};
			}
		} else {
			if(isset($this->_config->form->buttons->{$buttonName})) {
				$obj = $this->_config->form->buttons->{$buttonName};
			}
		}
		if($obj) {
			$this->setLabelTo($value, $obj);
		}
	}
	public function setFieldAttribute($fieldName, $attrName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setAttributeTo($attrName, $value, $this->_config->form->fields->{$fieldName});
		}
	}
	public function setFieldDefault($name, $value) {
		if(isset($this->_config->form->fields->{$name})) {
			$this->_config->form->fields->{$name}->value = $value;
		}
	}
	public function setFieldEmptyOption($fieldName, $label, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->_config->form->fields->{$fieldName}->emptyOption = new \stdClass();
			$this->_config->form->fields->{$fieldName}->emptyOption->label = $label;
			$this->_config->form->fields->{$fieldName}->emptyOption->value = $value;
		}
	}
	public function setFieldLabel($fieldName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setLabelTo($value, $this->_config->form->fields->{$fieldName});
		}
	}
	public function setMethod($method, $mode = false) {
		$this->setMainValue('method', $method, $mode);
	}
	public function setName($name) {
		$this->setMainValue('name', $name);
	}
	public function setType($type, $mode = false) {
		$this->setMainValue('type', $type, $mode);
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
	protected function setAttributeTo($name, $value, \stdClass &$object) {
		if(!isset($object->attrs)) {
			$object->attrs = new \stdClass();
		}

		if(!isset($object->attrs->{$name}) || $object->attrs->{$name} != $value) {
			$object->attrs->{$name} = $value;
			$this->_dirty = true;
		}
	}
	protected function setLabelTo($value, \stdClass &$object) {
		if(!isset($object->label) || $object->label != $value) {
			$object->label = $value;
			$this->_dirty = true;
		}
	}
	protected function setMainValue($name, $value, $mode = false) {
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
