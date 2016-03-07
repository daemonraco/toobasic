<?php

/**
 * @file Form.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class FormWriter
 * This class holds almost all the logic to modify a form specification file in a
 * controlled way.
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
	 * This method appends a button definition to a form specification file.
	 *
	 * @param string $name Button name.
	 * @param string $type Button type.
	 * @param string $mode Mode in which the button should appear.
	 * @param string $error In case of failure, this parameter will have the
	 * error message.
	 * @return boolean Returns TRUE if no error was found.
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
	 * This method appends a field specification to a form specification file.
	 *
	 * @param string $name Field name.
	 * @param string $type Field type.
	 * @param string $error In case of failure, this parameter will have the
	 * error message.
	 * @return boolean Returns TRUE if no error was found.
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
	 * This method allows to know if the form specification has been changed
	 * and have not been saved yet.
	 *
	 * @return boolean Returns TRUE if there are changes waiting to be saved.
	 */
	public function dirty() {
		return $this->_dirty;
	}
	/**
	 * This method adds a list of excluded modes for certain field.
	 *
	 * @param string $fieldName Field name.
	 * @param string[] $modes List of excluded mode names.
	 * @param string $error In case of failure, this parameter will have the
	 * error message.
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
	 * This method removes the 'action' configuration of a form specification
	 * file.
	 *
	 * @param string $mode Remove the specification from certain mode.
	 */
	public function removeAction($mode = false) {
		$this->removeMainValue('action', $mode);
	}
	/**
	 * This method removes a form attribute specification.
	 *
	 * @param string $name HTML attribute name.
	 * @param string $mode Mode on which this operation has to be performed.
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
	 * This method removes a button from a form specification.
	 *
	 * @param string $buttonName Button name.
	 * @param string $mode Mode on which this operation has to be performed.
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
	 * This method removes a button's attribute specification.
	 *
	 * @param type $buttonName Button name.
	 * @param type $attrName HTML attribute name.
	 * @param string $mode Mode on which this operation has to be performed.
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
	 * This method removes a button's label setting.
	 *
	 * @param type $buttonName Button name.
	 * @param string $mode Mode on which this operation has to be performed.
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
	 * This method removes certain field from a form specification file.
	 *
	 * @param string $fieldName Field name.
	 */
	public function removeField($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			unset($this->_config->form->fields->{$fieldName});
			$this->_dirty = true;
		}
	}
	/**
	 * This method removes a field's attribute specification.
	 *
	 * @param string $fieldName Field name.
	 * @param type $attrName HTML attribute name.
	 */
	public function removeFieldAttribute($fieldName, $attrName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->removeAttributeFrom($attrName, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * This method removes all exclusion specifications of certain field.
	 *
	 * @param string $fieldName Field name.
	 */
	public function removeFieldExcludedModes($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName}->excludedModes)) {
			unset($this->_config->form->fields->{$fieldName}->excludedModes);
			$this->_dirty = true;
		}
	}
	/**
	 * This method removes a field's label setting.
	 *
	 * @param string $fieldName Field name.
	 */
	public function removeFieldLabel($fieldName) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->removeLabelFrom($this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * This method removes the 'method' configuration of a form specification
	 * file.
	 *
	 * @param string $mode Remove the specification from certain mode.
	 */
	public function removeMethod($mode = false) {
		$this->removeMainValue('method', $mode);
	}
	/**
	 * This method removes the 'name' configuration of a form specification
	 * file.
	 */
	public function removeName() {
		$this->removeMainValue('name', false);
	}
	/**
	 * This method removes the 'name' configuration of a form specification
	 * file.
	 */
	public function removeType() {
		$this->removeMainValue('type', false);
	}
	/**
	 * This method sets the 'action' configuration to a form specification
	 * file.
	 *
	 * @param string $action URL to be set as 'action'.
	 * @param string $mode Adss the specification to certain mode.
	 */
	public function setAction($action, $mode = false) {
		$this->setMainValue('action', $action, $mode);
	}
	/**
	 * This method sets an attribute to be used the HTML tag '<form>'.
	 *
	 * @param string $name Attribute name.
	 * @param mixed $value Attribute value to be set.
	 * @param string $mode In case the attributes affects only a specific form
	 * mode.
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
	 * This method sets a button's attribute to be used when it's rendered.
	 *
	 * @param string $buttonName Button name.
	 * @param string $attrName Attribute name.
	 * @param mixed $value Attribute value to be set.
	 * @param string $mode In case the attributes affects only a specific form
	 * mode.
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
	 * This method sets a button's label to be used when it's rendered.
	 *
	 * @param string $buttonName Button name.
	 * @param string $value Label value to be set.
	 * @param string $mode In case the attributes affects only a specific form
	 * mode.
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
	 * This method sets a field's attribute to be used when it's rendered.
	 *
	 * @param type $fieldName Field name.
	 * @param type $attrName Attribute name.
	 * @param type $value Attribute value to be set.
	 */
	public function setFieldAttribute($fieldName, $attrName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setAttributeTo($attrName, $value, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * This method sets the default value to be shown when a field is
	 * displayed without a particular value.
	 *
	 * @param string $name Field name.
	 * @param string $value Value to be shown.
	 */
	public function setFieldDefault($name, $value) {
		if(isset($this->_config->form->fields->{$name})) {
			$this->_config->form->fields->{$name}->value = $value;
		}
	}
	/**
	 * This method sets the first option of a HTML tag '<select>'. Such option
	 * is considered as extra and it'll be used as default.
	 *
	 * @param string $fieldName Field name.
	 * @param string $label Extra option label.
	 * @param string $value Extra option value.
	 */
	public function setFieldEmptyOption($fieldName, $label, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->_config->form->fields->{$fieldName}->emptyOption = new \stdClass();
			$this->_config->form->fields->{$fieldName}->emptyOption->label = $label;
			$this->_config->form->fields->{$fieldName}->emptyOption->value = $value;
		}
	}
	/**
	 * This method sets the list of mode names in which certain field should
	 * not be shown.
	 *
	 * @param string $fieldName Field name.
	 * @param string[] $modes List of mode names.
	 */
	public function setFieldExcludedModes($fieldName, $modes) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->_config->form->fields->{$fieldName}->excludedModes = $modes;
			$this->_dirty = true;
		}
	}
	/**
	 * This method sets the label to be rendered beside certain field.
	 *
	 * @param string $fieldName Field name.
	 * @param string $value Label value.
	 */
	public function setFieldLabel($fieldName, $value) {
		if(isset($this->_config->form->fields->{$fieldName})) {
			$this->setLabelTo($value, $this->_config->form->fields->{$fieldName});
		}
	}
	/**
	 * This method sets the 'method' configuration of a form specification
	 * file.
	 *
	 * @param string $method Method to be set.
	 * @param string $mode Adds the specification to certain mode.
	 */
	public function setMethod($method, $mode = false) {
		$this->setMainValue('method', $method, $mode);
	}
	/**
	 * This method sets the 'name' of a form specification file.
	 *
	 * @param string $name Name to be set.
	 */
	public function setName($name) {
		$this->setMainValue('name', $name);
	}
	/**
	 * This method sets the 'type' of a form specification file.
	 *
	 * @param string $type Type name to be set.
	 * @param string $mode Mode on which this operation has to be performed.
	 */
	public function setType($type, $mode = false) {
		$this->setMainValue('type', $type, $mode);
	}
	/**
	 * This method updates the physical configuration file with all changes.
	 * If no change was made, the file will remain untouched.
	 *
	 * @return boolean Return TRUE if the file was modified without problems.
	 */
	public function save() {
		return $this->dirty() ? \boolval(file_put_contents($this->_path, json_encode($this->_config, JSON_PRETTY_PRINT))) : false;
	}
	//
	// Protected methods.
	/**
	 * This method loads a form configuration file.
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
	 * This method removes an attribute from certain configuration structure.
	 *
	 * @param string $name Attribute name.
	 * @param \stdClass $object Configuration structure to modify.
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
	 * This method removes a label from certain configuration structure.
	 *
	 * @param \stdClass $object Configuration structure to modify.
	 */
	protected function removeLabelFrom(\stdClass &$object) {
		if(isset($object->label)) {
			unset($object->label);
			$this->_dirty = true;
		}
	}
	/**
	 * This method removes a property's value used to build the HTML tag
	 * '<form>'.
	 *
	 * @param string $name Property's name.
	 * @param string $mode In case the property affects only a specific form
	 * mode.
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
	 * This method sets an attribute inside certain configuration structure.
	 *
	 * @param string $name Attribute name.
	 * @param mixed $value Attribute value to be set.
	 * @param \stdClass $object Configuration structure to modify.
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
	 * This method sets a label inside certain configuration structure.
	 *
	 * @param string $value Label value.
	 * @param \stdClass $object Configuration structure to modify.
	 */
	protected function setLabelTo($value, \stdClass &$object) {
		if(!isset($object->label) || $object->label != $value) {
			$object->label = $value;
			$this->_dirty = true;
		}
	}
	/**
	 * This method sets a property's value used to build the HTML tag
	 * '<form>'.
	 *
	 * @param string $name Property's name.
	 * @param mixed $value Value to be set.
	 * @param string $mode In case the property affects only a specific form
	 * mode.
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
