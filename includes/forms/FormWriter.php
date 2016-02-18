<?php

/**
 * @file Form.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class FormWriter
 * @TODO doc
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
	/**
	 * Class constructor.
	 *
	 * @param \TooBasic\Forms\Form $form Form to mange.
	 * @throws \TooBasic\Forms\FormsException
	 */
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
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $type @TODO doc
	 * @param type $mode @TODO doc
	 * @param type $error @TODO doc
	 * @return boolean @TODO doc
	 */
	public function addButton($name, $type, $mode = false, &$error = false) {
		$ok = true;
		//
		// Checking type.
		if(!in_array($type, array(GC_FORMS_BUTTONTYPE_BUTTON, GC_FORMS_BUTTONTYPE_RESET, GC_FORMS_BUTTONTYPE_SUBMIT))) {
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
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $type @TODO doc
	 * @param type $error @TODO doc
	 * @return boolean @TODO doc
	 */
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
	/**
	 * @TODO doc
	 *
	 * @return type @TODO doc
	 */
	public function dirty() {
		return $this->_dirty;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param array $modes @TODO doc
	 * @param type $error @TODO doc
	 */
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
	/**
	 *  @TODO doc
	 *
	 * @param type $mode @TODO doc
	 */
	public function removeAction($mode = false) {
		$this->removeMainValue('action', $mode);
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function removeAttribute($name, $mode = false) {
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
			$this->removeAttributeFrom($name, $obj);
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $buttonName @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function removeButton($buttonName, $mode = false) {
		//
		// Choosing the right object.
		if($mode) {
			if(isset($this->_config->form->modes->{$mode}->buttons->{$buttonName})) {
				unset($this->_config->form->modes->{$mode}->buttons->{$buttonName});
				$this->_dirty = true;
			}
		} else {
			if(isset($this->_config->form->buttons->{$buttonName})) {
				unset($this->_config->form->buttons->{$buttonName});
				$this->_dirty = true;
			}
		}
	}
	/**
	 *  @TODO doc
	 *
	 * @param type $buttonName @TODO doc
	 * @param type $attrName @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function removeButtonAttribute($buttonName, $attrName, $mode = false) {
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
			$this->removeAttributeFrom($attrName, $obj);
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $buttonName @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function removeButtonLabel($buttonName, $mode = false) {
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
			$this->removeLabelFrom($obj);
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 */
	public function removeField($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			unset($this->_config->form->fields->{$fieldName});
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param type $attrName @TODO doc
	 */
	public function removeFieldAttribute($fieldName, $attrName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->removeAttributeFrom($attrName, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 */
	public function removeFieldExcludedModes($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName}->excludedModes)) {
			unset($this->_config->form->fields->{$fieldName}->excludedModes);
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 */
	public function removeFieldLabel($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->removeLabelFrom($this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $mode @TODO doc
	 */
	public function removeMethod($mode = false) {
		$this->removeMainValue('method', $mode);
	}
	/**
	 * @TODO doc
	 */
	public function removeName() {
		$this->removeMainValue('name', false);
	}
	/**
	 * @TODO doc
	 */
	public function removeType() {
		$this->removeMainValue('type', false);
	}
	/**
	 * @TODO doc
	 *
	 * @param type $action @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function setAction($action, $mode = false) {
		$this->setMainValue('action', $action, $mode);
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $value @TODO doc
	 * @param type $mode @TODO doc
	 */
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
	/**
	 * @TODO doc
	 *
	 * @param type $buttonName @TODO doc
	 * @param type $attrName @TODO doc
	 * @param type $value @TODO doc
	 * @param type $mode @TODO doc
	 */
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
	/**
	 * @TODO doc
	 *
	 * @param type $buttonName @TODO doc
	 * @param type $value @TODO doc
	 * @param type $mode @TODO doc
	 */
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
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param type $attrName @TODO doc
	 * @param type $value @TODO doc
	 */
	public function setFieldAttribute($fieldName, $attrName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setAttributeTo($attrName, $value, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $value @TODO doc
	 */
	public function setFieldDefault($name, $value) {
		if(isset($this->_config->form->fields->{$name})) {
			$this->_config->form->fields->{$name}->value = $value;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param type $label @TODO doc
	 * @param type $value @TODO doc
	 */
	public function setFieldEmptyOption($fieldName, $label, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->_config->form->fields->{$fieldName}->emptyOption = new \stdClass();
			$this->_config->form->fields->{$fieldName}->emptyOption->label = $label;
			$this->_config->form->fields->{$fieldName}->emptyOption->value = $value;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param type $modes @TODO doc
	 */
	public function setFieldExcludedModes($fieldName, $modes) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->_config->form->fields->{$fieldName}->excludedModes = $modes;
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $fieldName @TODO doc
	 * @param type $value @TODO doc
	 */
	public function setFieldLabel($fieldName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setLabelTo($value, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $method @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function setMethod($method, $mode = false) {
		$this->setMainValue('method', $method, $mode);
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 */
	public function setName($name) {
		$this->setMainValue('name', $name);
	}
	/**
	 * @TODO doc
	 *
	 * @param type $type @TODO doc
	 * @param type $mode @TODO doc
	 */
	public function setType($type, $mode = false) {
		$this->setMainValue('type', $type, $mode);
	}
	/**
	 * @TODO doc
	 *
	 * @return type @TODO doc
	 */
	public function save() {
		return $this->dirty() ? \boolval(file_put_contents($this->_path, json_encode($this->_config, JSON_PRETTY_PRINT))) : false;
	}
	//
	// Protected methods.
	/**
	 * @TODO doc
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
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param \stdClass $object @TODO doc
	 */
	protected function removeAttributeFrom($name, \stdClass &$object) {
		if(!isset($object->attrs)) {
			$object->attrs = new \stdClass();
		}

		if(isset($object->attrs->{$name})) {
			unset($object->attrs->{$name});
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param \stdClass $object @TODO doc
	 */
	protected function removeLabelFrom(\stdClass &$object) {
		if(isset($object->label)) {
			unset($object->label);
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $mode @TODO doc
	 */
	protected function removeMainValue($name, $mode = false) {
		if($mode) {
			if(isset($this->_config->form->modes->{$mode}->{$name})) {
				unset($this->_config->form->modes->{$mode}->{$name});
				$this->_dirty = true;
			}
		} else {
			if(isset($this->_config->form->{$name})) {
				unset($this->_config->form->{$name});
				$this->_dirty = true;
			}
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $value @TODO doc
	 * @param \stdClass $object @TODO doc
	 */
	protected function setAttributeTo($name, $value, \stdClass &$object) {
		if(!isset($object->attrs)) {
			$object->attrs = new \stdClass();
		}

		if(!isset($object->attrs->{$name}) || $object->attrs->{$name} != $value) {
			$object->attrs->{$name} = $value;
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $value @TODO doc
	 * @param \stdClass $object @TODO doc
	 */
	protected function setLabelTo($value, \stdClass &$object) {
		if(!isset($object->label) || $object->label != $value) {
			$object->label = $value;
			$this->_dirty = true;
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $name @TODO doc
	 * @param type $value @TODO doc
	 * @param type $mode @TODO doc
	 */
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
