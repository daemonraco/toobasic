<?php

/**
 * @file forms.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Forms\Form;
use TooBasic\Forms\FormsManager;
use TooBasic\Forms\FormWriter;
use TooBasic\Shell\Color;
use TooBasic\Shell\Option;

/**
 * @class FormsSystool
 * This system shell tool provides a mechanism to manage form specifications.
 */
class FormsSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OPTION_ADD_BUTTON = 'AddButton';
	const OPTION_ADD_FIELD = 'AddField';
	const OPTION_CREATE = 'Create';
	const OPTION_DESCRIBE = 'Describe';
	const OPTION_FALSE = 'False';
	const OPTION_FORM = 'Form';
	const OPTION_MODE = 'Mode';
	const OPTION_MODULE = 'Module';
	const OPTION_NAME = 'Name';
	const OPTION_REMOVE = 'Remove';
	const OPTION_REMOVE_ACTION = 'RemoveAction';
	const OPTION_REMOVE_BUTTON = 'RemoveButton';
	const OPTION_REMOVE_BUTTON_ATTRIBUTE = 'RemoveButtonAttribute';
	const OPTION_REMOVE_BUTTON_LABEL = 'RemoveButtonLabel';
	const OPTION_REMOVE_FIELD = 'RemoveField';
	const OPTION_REMOVE_FIELD_ATTRIBUTE = 'RemoveFieldAttribute';
	const OPTION_REMOVE_FIELD_EXCLUDED_MODES = 'RemoveFieldExcludedModes';
	const OPTION_REMOVE_FIELD_LABEL = 'RemoveFieldLabel';
	const OPTION_REMOVE_FORM_ATTRIBUTE = 'RemoveFormAttribute';
	const OPTION_REMOVE_METHOD = 'RemoveMethod';
	const OPTION_REMOVE_NAME = 'RemoveName';
	const OPTION_SET_ACTION = 'SetAction';
	const OPTION_SET_BUTTON_ATTRIBUTE = 'SetButtonAttribute';
	const OPTION_SET_BUTTON_LABEL = 'SetButtonLabel';
	const OPTION_SET_FIELD_ATTRIBUTE = 'SetFieldAttribute';
	const OPTION_SET_FIELD_EXCLUDED_MODES = 'SetFieldExcludedModes';
	const OPTION_SET_FIELD_LABEL = 'SetFieldLabel';
	const OPTION_SET_FORM_ATTRIBUTE = 'SetFormAttribute';
	const OPTION_SET_METHOD = 'SetMethod';
	const OPTION_SET_NAME = 'SetName';
	const OPTION_SET_TYPE = 'SetType';
	const OPTION_TRUE = 'True';
	const OPTION_TYPE = 'Type';
	const OPTION_VALUE = 'Value';
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Forms\FormsManager Forms manager shortcut.
	 */
	protected $_formsHelper = false;
	//
	// Protected methods.
	protected function loadHelpers() {
		//
		// Avoiding multipe loads.
		if($this->_formsHelper === false) {
			$this->_formsHelper = FormsManager::Instance();
		}
	}
	protected function setOptions() {
		//
		// Global dependencies.
		global $Defaults;

		$this->_options->setHelpText("This tool allows you to create, modify and remove Forms Builder specification files.");

		$text = "This option creates a basic Forms Builder specification file.\n";
		$text.= "It can be use with '--module' to generate the specification inside certain module.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_CREATE, ['create', 'new', 'add'], Option::TYPE_VALUE, $text, 'form-name'));

		$text = "This option removes a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE, ['remove', 'rm', 'delete'], Option::TYPE_VALUE, $text, 'form-name'));

		$text = "This option display a Forms Builder specification information.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_DESCRIBE, ['--describe', '-d'], Option::TYPE_VALUE, $text, 'form-name'));

		$text = "This option sets the configuration for the attribute 'action' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_ACTION, ['--set-action', '-sA'], Option::TYPE_VALUE, $text, 'form-action'));

		$text = "This option sets the configuration for the attribute 'method' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_METHOD, ['--set-method', '-sM'], Option::TYPE_VALUE, $text, 'form-method'));

		$text = "This option sets a name to a form to be used for ID and other main properties (it doesn't change file names).\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_NAME, ['--set-name', '-sN'], Option::TYPE_VALUE, $text, 'new-form-name'));

		$text = "This option sets the form's type. It must be use along with option '--form'\n";
		$text.= "Available values are:";
		foreach(array_keys($Defaults[GC_DEFAULTS_FORMS_TYPES]) as $type) {
			$text.= "\n\t- '{$type}'";
		}
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_TYPE, ['--set-type', '-sT'], Option::TYPE_VALUE, $text, 'form-type'));

		$text = "This option appends a new field to a form specification.\n";
		$text.= "It requires options:\n";
		$text.= "\t'--form': Specifying form's name.\n";
		$text.= "\t'--type': Specifying a type for the field.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--value': Specifying a default value.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_ADD_FIELD, ['--add-field', '-af'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option appends a new button to a form specification.\n";
		$text.= "It requires options:\n";
		$text.= "\t'--form': Specifying form's name.\n";
		$text.= "\t'--type': Specifying a type for the field.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_ADD_BUTTON, ['--add-button', '-ab'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = "This option sets a specific form attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value' or '--true': Attribute's value.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_FORM_ATTRIBUTE, ['--set-attribute', '-sa'], Option::TYPE_VALUE, $text, 'attribute-name'));

		$text = "This option sets a specific field attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "\t'--value' or '--true': Attribute's value.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_FIELD_ATTRIBUTE, ['--set-field-attribute', '-sfa'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option sets a field's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_FIELD_LABEL, ['--set-field-label', '-sfl'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option sets a field's list of excluded modes. Provided value should be a comma separated string.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_FIELD_EXCLUDED_MODES, ['--set-field-exmodes', '-sfem'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option sets a specific button attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "\t'--value' or '--true': Attribute's value.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_BUTTON_ATTRIBUTE, ['--set-button-attribute', '-sba'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = "This option sets a button's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SET_BUTTON_LABEL, ['--set-button-label', '-sbl'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = "This option removes the configuration for the attribute 'action' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_ACTION, ['--remove-action', '-rA'], Option::TYPE_NO_VALUE, $text));

		$text = "This option removes the configuration for the attribute 'method' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_METHOD, ['--remove-method', '-rM'], Option::TYPE_NO_VALUE, $text));

		$text = "This option removes the name to be used on a form for ID and other main properties (it doesn't change file names).\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_NAME, ['--remove-name', '-rN'], Option::TYPE_NO_VALUE, $text));

		$text = "This option removes a field from a form specification.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_FIELD, ['--remove-field', '-rf'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option removes a button from a form specification.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_BUTTON, ['--remove-button', '-rb'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = "This option removes a specific form attribute.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_FORM_ATTRIBUTE, ['--remove-attribute', '-ra'], Option::TYPE_VALUE, $text, 'attribute-name'));

		$text = "This option removes a specific field attribute.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_FIELD_ATTRIBUTE, ['--remove-field-attribute', '-rfa'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option removes a field's list of excluded modes.";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_FIELD_EXCLUDED_MODES, ['--remove-field-exmodes', '-rfem'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option removes a field's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_FIELD_LABEL, ['--remove-field-label', '-rfl'], Option::TYPE_VALUE, $text, 'field-name'));

		$text = "This option removes a specific button attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_BUTTON_ATTRIBUTE, ['--remove-button-attribute', '-rba'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = "This option removes a button's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE_BUTTON_LABEL, ['--remove-button-label', '-rbl'], Option::TYPE_VALUE, $text, 'button-name'));

		$text = 'Indicates which form building mode is being affected by current command.';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_MODE, ['--mode', '-m'], Option::TYPE_VALUE, $text, 'form-mode'));

		$text = 'Indicates which form is being affected by current command.';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FORM, ['--form', '-f'], Option::TYPE_VALUE, $text, 'form-name'));

		$text = "Some specific name required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_NAME, ['--name', '-n'], Option::TYPE_VALUE, $text, 'name'));

		$text = "Some specific value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_VALUE, ['--value', '-v'], Option::TYPE_VALUE, $text, 'value'));

		$text = "Some negative value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FALSE, ['--false', '-no', '-N'], Option::TYPE_NO_VALUE, $text));

		$text = "Some positive value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_TRUE, ['--true', '-yes', '-Y'], Option::TYPE_NO_VALUE, $text));

		$text = "Some specific type required by another option.\n";
		$text.= "When used with '--add-field' available options are:\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_INPUT."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_PASSWORD."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_TEXT."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_ENUM."' (it should be used as: 'enum:VALUE1:VALUE2:OTHERVALUE')\n";
		$text.= "When used with '--add-button' available options are:\n";
		$text.= "\t- '".GC_FORMS_BUTTONTYPE_SUBMIT."'\n";
		$text.= "\t- '".GC_FORMS_BUTTONTYPE_RESET."'\n";
		$text.= "\t- '".GC_FORMS_BUTTONTYPE_BUTTON."' (default)";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_TYPE, ['--type', '-t'], Option::TYPE_VALUE, $text, 'type'));

		$text = "Generate files inside a module.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_MODULE, ['--module', '-M'], Option::TYPE_VALUE, $text, 'module-name'));
	}
	protected function taskAddButton($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_ADD_BUTTON};
		$buttonType = $this->params->opt->{self::OPTION_TYPE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} elseif(!$buttonType) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No button type specified");
		} elseif(!in_array($buttonType, [GC_FORMS_BUTTONTYPE_BUTTON, GC_FORMS_BUTTONTYPE_RESET, GC_FORMS_BUTTONTYPE_SUBMIT])) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "Invalid button type specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Adding button '{$buttonName}' to form '{$formName}': ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$error = false;
				$writer = new FormWriter($form);
				$writer->addButton($buttonName, $buttonType, $formMode, $error);

				if($writer->dirty() || $error) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed").' (Error: '.Color::Yellow($error).")\n";
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskAddField($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_ADD_FIELD};
		$fieldType = $this->params->opt->{self::OPTION_TYPE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} elseif(!$fieldType) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No field type specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Adding field '{$fieldName}' to form '{$formName}': ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$error = false;
				$writer = new FormWriter($form);
				$writer->addField($fieldName, $fieldType, $error);
				if(isset($this->params->opt->{self::OPTION_VALUE})) {
					$writer->setFieldDefault($fieldName, $this->params->opt->{self::OPTION_VALUE});
				}

				if($writer->dirty() || $error) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed").' (Error: '.Color::Yellow($error).")\n";
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskDescribe($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OPTION_DESCRIBE};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Checking parameters.
		$form = new Form($name);
		if($form->path()) {
			//
			// Descriving form.
			echo "{$spacer}Describing form '{$name}':\n";
			echo "{$spacer}\tType:       '".Color::Green($form->type())."'\n";
			echo "{$spacer}\tAction:     '".Color::Green($form->action())."'\n";
			echo "{$spacer}\tMethod:     '".Color::Green($form->method())."'\n";
			echo "{$spacer}\tRead-Only:  '".Color::Green($form->isReadOnly() ? 'Yes' : 'No')."'\n";
			$attrs = get_object_vars($form->attributes());
			if($attrs) {
				echo "{$spacer}\tAttributes:\n";
				foreach($attrs as $k => $v) {
					echo "{$spacer}\t\t'".Color::Green($k)."': '".Color::Yellow($v)."'\n";
				}
			}

			$modes = $form->modes();
			sort($modes);
			foreach($modes as $mode) {
				echo "\n{$spacer}\tIn mode '".Color::Yellow($mode)."':\n";
				echo "{$spacer}\t\tAction:     '".Color::Green($form->action($mode))."'\n";
				echo "{$spacer}\t\tMethod:     '".Color::Green($form->method($mode))."'\n";
				echo "{$spacer}\t\tRead-Only:  '".Color::Green($form->isReadOnly($mode) ? 'Yes' : 'No')."'\n";
				$attrs = get_object_vars($form->attributes($mode));
				if($attrs) {
					echo "{$spacer}\t\tAttributes:\n";
					foreach($attrs as $k => $v) {
						echo "{$spacer}\t\t\t'".Color::Green($k)."': '".Color::Yellow($v)."'\n";
					}
				}
			}

			$fields = $form->fields();
			echo "{$spacer}Fields:\n";
			foreach($fields as $fieldName) {
				$fieldType = $form->fieldType($fieldName);
				$fieldValue = $form->fieldValue($fieldName);

				echo "{$spacer}\tField '".Color::Green($fieldName)."':\n";
				echo "{$spacer}\t\tType:            '".Color::Green($fieldType)."':\n";
				echo "{$spacer}\t\tID:              '".Color::Green($form->fieldId($fieldName))."':\n";
				echo "{$spacer}\t\tLabel:           '".Color::Green($form->fieldLabel($fieldName))."':\n";
				if($fieldType == GC_FORMS_FIELDTYPE_ENUM) {
					echo "{$spacer}\t\tPossible values: '".Color::Green(implode("', '", $form->fieldValues($fieldName)))."'\n";
				}
				if($fieldValue) {
					echo "{$spacer}\t\tDefault:         '".Color::Green($fieldValue)."':\n";
				}
				$excludedModes = $form->fieldExcludedModes($fieldName);
				if($excludedModes) {
					echo "{$spacer}\t\tExcluded Modes:  '".Color::Green(implode("', '", $excludedModes))."'\n";
				}
				$attrs = get_object_vars($form->fieldAttributes($fieldName));
				if($attrs) {
					echo "{$spacer}\t\tAttributes:\n";
					foreach($attrs as $k => $v) {
						echo "{$spacer}\t\t\t'".Color::Green($k)."': '".Color::Yellow($v)."'\n";
					}
				}

				echo "\n";
			}

			$buttonModes = array_merge([false], $modes);
			foreach($buttonModes as $mode) {
				$buttons = $form->buttonsFor($mode);
				if($buttons) {
					if($mode === false) {
						echo "{$spacer}Default Buttons:\n";
					} else {
						echo "{$spacer}Buttons for mode '".Color::Yellow($mode)."':\n";
					}

					foreach($buttons as $buttonName) {
						echo "{$spacer}\tButton '".Color::Green($buttonName)."':\n";
						echo "{$spacer}\t\tType:            '".Color::Green($form->buttonType($buttonName, $mode))."':\n";
						echo "{$spacer}\t\tID:              '".Color::Green($form->buttonId($buttonName))."':\n";
						echo "{$spacer}\t\tLabel:           '".Color::Green($form->buttonLabel($buttonName, $mode))."':\n";
						$attrs = get_object_vars($form->buttonAttributes($buttonName, $mode));
						if($attrs) {
							echo "{$spacer}\t\tAttributes:\n";
							foreach($attrs as $k => $v) {
								echo "{$spacer}\t\t\t'".Color::Green($k)."': '".Color::Yellow($v)."'\n";
							}
						}

						echo "\n";
					}
				}
			}
		} else {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "Unknown form called '{$name}'");
		}
	}
	protected function taskCreate($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OPTION_CREATE};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Checking parameters.
		$module = $this->params->opt->{self::OPTION_MODULE};
		//
		// Creating form.
		echo "{$spacer}Creating form '{$name}': ";
		$result = $this->_formsHelper->createForm($name, $module);
		if($result[GC_AFIELD_STATUS]) {
			echo Color::Green('Done')." (Path: {$result[GC_AFIELD_PATH]})\n";
		} else {
			echo Color::Red('Failed').' (Error: '.Color::Yellow($result[GC_AFIELD_ERROR]).")\n";
		}
	}
	protected function taskRemove($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OPTION_REMOVE};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Removing form.
		echo "{$spacer}Removing form '{$name}': ";
		$result = $this->_formsHelper->removeForm($name);
		if($result[GC_AFIELD_STATUS]) {
			echo Color::Green('Done')." (Path: {$result[GC_AFIELD_PATH]})\n";
		} else {
			echo Color::Red('Failed').' (Error: '.Color::Yellow($result[GC_AFIELD_ERROR]).")\n";
		}
	}
	protected function taskRemoveAction($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing form '{$formName}' action";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeAction($formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveButton($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_REMOVE_BUTTON};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing button '{$buttonName}' from form '{$formName}': ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$error = false;
				$writer = new FormWriter($form);
				$writer->removeButton($buttonName, $formMode);

				if($writer->dirty() || $error) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed").' (Error: '.Color::Yellow($error).")\n";
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveButtonAttribute($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_REMOVE_BUTTON_ATTRIBUTE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$attrName = $this->params->opt->{self::OPTION_NAME};
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing button '{$buttonName}' attribute '{$attrName}' (in form '{$formName}'";
			if($formMode) {
				echo " and mode '{$formMode}'): ";
			} else {
				echo "): ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeButtonAttribute($buttonName, $attrName, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveButtonLabel($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_REMOVE_BUTTON_LABEL};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing button '{$buttonName}' label (in form '{$formName}'";
			if($formMode) {
				echo " and mode '{$formMode}'): ";
			} else {
				echo "): ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeButtonLabel($buttonName, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveField($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_REMOVE_FIELD};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing field '{$fieldName}' from form '{$formName}': ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$error = false;
				$writer = new FormWriter($form);
				$writer->removeField($fieldName);

				if($writer->dirty() || $error) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed").' (Error: '.Color::Yellow($error).")\n";
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveFieldAttribute($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_REMOVE_FIELD_ATTRIBUTE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$attrName = $this->params->opt->{self::OPTION_NAME};
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing field '{$fieldName}' attribute '{$attrName}' (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeFieldAttribute($fieldName, $attrName);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveFieldExcludedModes($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_REMOVE_FIELD_EXCLUDED_MODES};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing field '{$fieldName}' excluded modes (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeFieldExcludedModes($fieldName);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveFieldLabel($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_REMOVE_FIELD_LABEL};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing field '{$fieldName}' label (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeFieldLabel($fieldName);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveFormAttribute($spacer = "") {
		//
		// Default values.
		$attrName = $this->params->opt->{self::OPTION_REMOVE_FORM_ATTRIBUTE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing form '{$formName}' attribute '{$attrName}'";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeAttribute($attrName, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveMethod($spacer = "") {
		//
		// Default values.
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing form '{$formName}' method";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeMethod($formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskRemoveName($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Removing form '{$formName}' name: ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->removeName();

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetAction($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$formAction = $this->params->opt->{self::OPTION_SET_ACTION};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$formName}' action";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setAction($formAction, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetButtonAttribute($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_SET_BUTTON_ATTRIBUTE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$attrName = $this->params->opt->{self::OPTION_NAME};
		$attrValue = $this->params->opt->{self::OPTION_VALUE} ? $this->params->opt->{self::OPTION_VALUE} : (isset($this->params->opt->{self::OPTION_TRUE}) ? true : false);
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting button '{$buttonName}' attribute '{$attrName}' (in form '{$formName}'";
			if($formMode) {
				echo " and mode '{$formMode}'): ";
			} else {
				echo "): ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setButtonAttribute($buttonName, $attrName, $attrValue, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetButtonLabel($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OPTION_SET_BUTTON_LABEL};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$labelValue = $this->params->opt->{self::OPTION_VALUE};
		//
		// Checking params.
		if(!$labelValue) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting button '{$buttonName}' label (in form '{$formName}'";
			if($formMode) {
				echo " and mode '{$formMode}'): ";
			} else {
				echo "): ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setButtonLabel($buttonName, $labelValue, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetFieldAttribute($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_SET_FIELD_ATTRIBUTE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$attrName = $this->params->opt->{self::OPTION_NAME};
		$attrValue = $this->params->opt->{self::OPTION_VALUE} ? $this->params->opt->{self::OPTION_VALUE} : (isset($this->params->opt->{self::OPTION_TRUE}) ? true : false);
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting field '{$fieldName}' attribute '{$attrName}' (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setFieldAttribute($fieldName, $attrName, $attrValue);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetFieldExcludedModes($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_SET_FIELD_EXCLUDED_MODES};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$modeValues = $this->params->opt->{self::OPTION_VALUE};
		//
		// Checking params.
		if(!$modeValues) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			$modeValues = explode(':', str_replace(',', ':', $modeValues));
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting field '{$fieldName}' excluded modes (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setFieldExcludedModes($fieldName, $modeValues);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetFieldLabel($spacer = "") {
		//
		// Default values.
		$fieldName = $this->params->opt->{self::OPTION_SET_FIELD_LABEL};
		$formName = $this->params->opt->{self::OPTION_FORM};
		$labelValue = $this->params->opt->{self::OPTION_VALUE};
		//
		// Checking params.
		if(!$labelValue) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting field '{$fieldName}' label (in form '{$formName}'): ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setFieldLabel($fieldName, $labelValue);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetFormAttribute($spacer = "") {
		//
		// Default values.
		$attrName = $this->params->opt->{self::OPTION_SET_FORM_ATTRIBUTE};
		$attrValue = isset($this->params->opt->{self::OPTION_VALUE}) ? $this->params->opt->{self::OPTION_VALUE} : (isset($this->params->opt->{self::OPTION_TRUE}) ? true : false);
		$formName = $this->params->opt->{self::OPTION_FORM};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No attribute value specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$formName}' attribute '{$attrName}'";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setAttribute($attrName, $attrValue, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetMethod($spacer = "") {
		//
		// Default values.
		$formMethod = $this->params->opt->{self::OPTION_SET_METHOD};
		$formMode = $this->params->opt->{self::OPTION_MODE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$formName}' method";
			if($formMode) {
				echo " (for mode '{$formMode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setMethod($formMethod, $formMode);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetName($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OPTION_SET_NAME};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$formName}' name: ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setName($name);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
	protected function taskSetType($spacer = "") {
		//
		// Default values.
		$formType = $this->params->opt->{self::OPTION_SET_TYPE};
		$formName = $this->params->opt->{self::OPTION_FORM};
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form name specified");
		} elseif(!$formType) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "No form type specified");
		} elseif(!isset($Defaults[GC_DEFAULTS_FORMS_TYPES][$formType])) {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "Unknown type '{$formType}'");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$formName}' type: ";
			//
			// Loading form.
			$form = new Form($formName);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setType($formType);

				if($writer->dirty()) {
					if($writer->save()) {
						echo Color::Green("Done\n");
					} else {
						echo Color::Red("Failed\n");
					}
				} else {
					echo Color::Yellow('Ignored')." (No changes were made)\n";
				}
			}
		}
	}
}
