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
 * @todo doc
 */
class FormsSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionAddButton = 'AddButton';
	const OptionAddField = 'AddField';
	const OptionCreate = 'Create';
	const OptionDescribe = 'Describe';
	const OptionFalse = 'False';
	const OptionForm = 'Form';
	const OptionMode = 'Mode';
	const OptionModule = 'Module';
	const OptionName = 'Name';
	const OptionRemove = 'Remove';
	const OptionRemoveAction = 'RemoveAction';
	const OptionRemoveButton = 'RemoveButton';
	const OptionRemoveButtonAttribute = 'RemoveButtonAttribute';
	const OptionRemoveButtonLabel = 'RemoveButtonLabel';
	const OptionRemoveField = 'RemoveField';
	const OptionRemoveFieldAttribute = 'RemoveFieldAttribute';
	const OptionRemoveFieldExcludedModes = 'RemoveFieldExcludedModes';
	const OptionRemoveFieldLabel = 'RemoveFieldLabel';
	const OptionRemoveFormAttribute = 'RemoveFormAttribute';
	const OptionRemoveMethod = 'RemoveMethod';
	const OptionRemoveName = 'RemoveName';
	const OptionSetAction = 'SetAction';
	const OptionSetButtonAttribute = 'SetButtonAttribute';
	const OptionSetButtonLabel = 'SetButtonLabel';
	const OptionSetFieldAttribute = 'SetFieldAttribute';
	const OptionSetFieldExcludedModes = 'SetFieldExcludedModes';
	const OptionSetFieldLabel = 'SetFieldLabel';
	const OptionSetFormAttribute = 'SetFormAttribute';
	const OptionSetMethod = 'SetMethod';
	const OptionSetName = 'SetName';
	const OptionSetType = 'SetType';
	const OptionTrue = 'True';
	const OptionType = 'Type';
	const OptionValue = 'Value';
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
		$this->_options->addOption(Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), Option::TypeValue, $text, 'form-name'));

		$text = "This option removes a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), Option::TypeValue, $text, 'form-name'));

		$text = "This option display a Forms Builder specification information.";
		$this->_options->addOption(Option::EasyFactory(self::OptionDescribe, array('--describe', '-d'), Option::TypeValue, $text, 'form-name'));

		$text = "This option sets the configuration for the attribute 'action' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetAction, array('--set-action', '-sA'), Option::TypeValue, $text, 'form-action'));

		$text = "This option sets the configuration for the attribute 'method' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetMethod, array('--set-method', '-sM'), Option::TypeValue, $text, 'form-method'));

		$text = "This option sets a name to a form to be used for ID and other main properties (it doesn't change file names).\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetName, array('--set-name', '-sN'), Option::TypeValue, $text, 'new-form-name'));

		$text = "This option sets the form's type. It must be use along with option '--form'\n";
		$text.= "Available values are:";
		foreach(array_keys($Defaults[GC_DEFAULTS_FORMS_TYPES]) as $type) {
			$text.= "\n\t- '{$type}'";
		}
		$this->_options->addOption(Option::EasyFactory(self::OptionSetType, array('--set-type', '-sT'), Option::TypeValue, $text, 'form-type'));

		$text = "This option appends a new field to a form specification.\n";
		$text.= "It requires options:\n";
		$text.= "\t'--form': Specifying form's name.\n";
		$text.= "\t'--type': Specifying a type for the field.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--value': Specifying a default value.";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddField, array('--add-field', '-af'), Option::TypeValue, $text, 'field-name'));

		$text = "This option appends a new button to a form specification.\n";
		$text.= "It requires options:\n";
		$text.= "\t'--form': Specifying form's name.\n";
		$text.= "\t'--type': Specifying a type for the field.";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddButton, array('--add-button', '-ab'), Option::TypeValue, $text, 'button-name'));

		$text = "This option sets a specific form attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value' or '--true': Attribute's value.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFormAttribute, array('--set-attribute', '-sa'), Option::TypeValue, $text, 'attribute-name'));

		$text = "This option sets a specific field attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "\t'--value' or '--true': Attribute's value.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFieldAttribute, array('--set-field-attribute', '-sfa'), Option::TypeValue, $text, 'field-name'));

		$text = "This option sets a field's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFieldLabel, array('--set-field-label', '-sfl'), Option::TypeValue, $text, 'field-name'));

		$text = "This option sets a field's list of excluded modes. Provided value should be a comma separated string.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFieldExcludedModes, array('--set-field-exmodes', '-sfem'), Option::TypeValue, $text, 'field-name'));

		$text = "This option sets a specific button attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "\t'--value' or '--true': Attribute's value.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetButtonAttribute, array('--set-button-attribute', '-sba'), Option::TypeValue, $text, 'button-name'));

		$text = "This option sets a button's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--value': Label to set.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetButtonLabel, array('--set-button-label', '-sbl'), Option::TypeValue, $text, 'button-name'));

		$text = "This option removes the configuration for the attribute 'action' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveAction, array('--remove-action', '-rA'), Option::TypeNoValue, $text));

		$text = "This option removes the configuration for the attribute 'method' of a form.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveMethod, array('--remove-method', '-rM'), Option::TypeNoValue, $text));

		$text = "This option removes the name to be used on a form for ID and other main properties (it doesn't change file names).\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveName, array('--remove-name', '-rN'), Option::TypeNoValue, $text));

		$text = "This option removes a field from a form specification.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveField, array('--remove-field', '-rf'), Option::TypeValue, $text, 'field-name'));

		$text = "This option removes a button from a form specification.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButton, array('--remove-button', '-rb'), Option::TypeValue, $text, 'button-name'));

		$text = "This option removes a specific form attribute.\n";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFormAttribute, array('--remove-attribute', '-ra'), Option::TypeValue, $text, 'attribute-name'));

		$text = "This option removes a specific field attribute.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFieldAttribute, array('--remove-field-attribute', '-rfa'), Option::TypeValue, $text, 'field-name'));

		$text = "This option removes a field's list of excluded modes.";
		$text.= "It must be use along with option '--form'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFieldExcludedModes, array('--remove-field-exmodes', '-rfem'), Option::TypeValue, $text, 'field-name'));

		$text = "This option removes a field's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFieldLabel, array('--remove-field-label', '-rfl'), Option::TypeValue, $text, 'field-name'));

		$text = "This option removes a specific button attribute value.\n";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButtonAttribute, array('--remove-button-attribute', '-rba'), Option::TypeValue, $text, 'button-name'));

		$text = "This option removes a button's label.";
		$text.= "It must be use along with options:\n";
		$text.= "\t'--form': Name of the form to modify.\n";
		$text.= "\t'--name': Attribute's name.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--mode'";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButtonLabel, array('--remove-button-label', '-rbl'), Option::TypeValue, $text, 'button-name'));

		$text = 'Indicates which form building mode is being affected by current command.';
		$this->_options->addOption(Option::EasyFactory(self::OptionMode, array('--mode', '-m'), Option::TypeValue, $text, 'form-mode'));

		$text = 'Indicates which form is being affected by current command.';
		$this->_options->addOption(Option::EasyFactory(self::OptionForm, array('--form', '-f'), Option::TypeValue, $text, 'form-name'));

		$text = "Some specific name required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionName, array('--name', '-n'), Option::TypeValue, $text, 'name'));

		$text = "Some specific value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionValue, array('--value', '-v'), Option::TypeValue, $text, 'value'));

		$text = "Some negative value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionFalse, array('--false', '-no', '-N'), Option::TypeNoValue, $text));

		$text = "Some positive value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionTrue, array('--true', '-yes', '-Y'), Option::TypeNoValue, $text));

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
		$this->_options->addOption(Option::EasyFactory(self::OptionType, array('--type', '-t'), Option::TypeValue, $text, 'type'));

		$text = "Generate files inside a module.";
		$this->_options->addOption(Option::EasyFactory(self::OptionModule, array('--module', '-M'), Option::TypeValue, $text, 'module-name'));
	}
	protected function taskAddButton($spacer = "") {
		//
		// Default values.
		$buttonName = $this->params->opt->{self::OptionAddButton};
		$buttonType = $this->params->opt->{self::OptionType};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
		} elseif(!$buttonType) {
			$this->setError(self::ErrorWrongParameters, "No button type specified");
		} elseif(!in_array($buttonType, array(GC_FORMS_BUTTONTYPE_BUTTON, GC_FORMS_BUTTONTYPE_RESET, GC_FORMS_BUTTONTYPE_SUBMIT))) {
			$this->setError(self::ErrorWrongParameters, "Invalid button type specified");
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
		$fieldName = $this->params->opt->{self::OptionAddField};
		$fieldType = $this->params->opt->{self::OptionType};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
		} elseif(!$fieldType) {
			$this->setError(self::ErrorWrongParameters, "No field type specified");
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
				if(isset($this->params->opt->{self::OptionValue})) {
					$writer->setFieldDefault($fieldName, $this->params->opt->{self::OptionValue});
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
		$name = $this->params->opt->{self::OptionDescribe};
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

			$buttonModes = array_merge(array(false), $modes);
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
			$this->setError(self::ErrorWrongParameters, "Unknown form called '{$name}'");
		}
	}
	protected function taskCreate($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OptionCreate};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Checking parameters.
		$module = $this->params->opt->{self::OptionModule};
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
		$name = $this->params->opt->{self::OptionRemove};
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
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$buttonName = $this->params->opt->{self::OptionRemoveButton};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$buttonName = $this->params->opt->{self::OptionRemoveButtonAttribute};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		$attrName = $this->params->opt->{self::OptionName};
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$buttonName = $this->params->opt->{self::OptionRemoveButtonLabel};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionRemoveField};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionRemoveFieldAttribute};
		$formName = $this->params->opt->{self::OptionForm};
		$attrName = $this->params->opt->{self::OptionName};
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionRemoveFieldExcludedModes};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionRemoveFieldLabel};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$attrName = $this->params->opt->{self::OptionRemoveFormAttribute};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$formMode = $this->params->opt->{self::OptionMode};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		$formAction = $this->params->opt->{self::OptionSetAction};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$buttonName = $this->params->opt->{self::OptionSetButtonAttribute};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		$attrName = $this->params->opt->{self::OptionName};
		$attrValue = $this->params->opt->{self::OptionValue} ? $this->params->opt->{self::OptionValue} : (isset($this->params->opt->{self::OptionTrue}) ? true : false);
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$buttonName = $this->params->opt->{self::OptionSetButtonLabel};
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		$labelValue = $this->params->opt->{self::OptionValue};
		//
		// Checking params.
		if(!$labelValue) {
			$this->setError(self::ErrorWrongParameters, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionSetFieldAttribute};
		$formName = $this->params->opt->{self::OptionForm};
		$attrName = $this->params->opt->{self::OptionName};
		$attrValue = $this->params->opt->{self::OptionValue} ? $this->params->opt->{self::OptionValue} : (isset($this->params->opt->{self::OptionTrue}) ? true : false);
		//
		// Checking params.
		if(!$attrName) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ErrorWrongParameters, "No attribute name specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionSetFieldExcludedModes};
		$formName = $this->params->opt->{self::OptionForm};
		$modeValues = $this->params->opt->{self::OptionValue};
		//
		// Checking params.
		if(!$modeValues) {
			$this->setError(self::ErrorWrongParameters, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$fieldName = $this->params->opt->{self::OptionSetFieldLabel};
		$formName = $this->params->opt->{self::OptionForm};
		$labelValue = $this->params->opt->{self::OptionValue};
		//
		// Checking params.
		if(!$labelValue) {
			$this->setError(self::ErrorWrongParameters, "No label value specified");
		} elseif(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$attrName = $this->params->opt->{self::OptionSetFormAttribute};
		$attrValue = isset($this->params->opt->{self::OptionValue}) ? $this->params->opt->{self::OptionValue} : (isset($this->params->opt->{self::OptionTrue}) ? true : false);
		$formName = $this->params->opt->{self::OptionForm};
		$formMode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
		} elseif(!$attrValue) {
			$this->setError(self::ErrorWrongParameters, "No attribute value specified");
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
		$formMethod = $this->params->opt->{self::OptionSetMethod};
		$formMode = $this->params->opt->{self::OptionMode};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$name = $this->params->opt->{self::OptionSetName};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
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
		$formType = $this->params->opt->{self::OptionSetType};
		$formName = $this->params->opt->{self::OptionForm};
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking params.
		if(!$formName) {
			$this->setError(self::ErrorWrongParameters, "No form name specified");
		} elseif(!$formType) {
			$this->setError(self::ErrorWrongParameters, "No form type specified");
		} elseif(!isset($Defaults[GC_DEFAULTS_FORMS_TYPES][$formType])) {
			$this->setError(self::ErrorWrongParameters, "Unknown type '{$formType}'");
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
