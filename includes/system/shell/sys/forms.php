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
	const OptionMode = 'Mode';
	const OptionModule = 'Module';
	const OptionName = 'Name';
	const OptionRemove = 'Remove';
	const OptionRemoveAction = 'RemoveAction';
	const OptionRemoveButton = 'RemoveButton';
	const OptionRemoveButtonAttribute = 'RemoveButtonAttribute';
	const OptionRemoveField = 'RemoveField';
	const OptionRemoveFieldAttribute = 'RemoveFieldAttribute';
	const OptionRemoveFormAttribute = 'RemoveFormAttribute';
	const OptionRemoveMethod = 'RemoveMethod';
	const OptionRemoveName = 'RemoveName';
	const OptionSetAction = 'SetAction';
	const OptionSetButtonAttribute = 'SetButtonAttribute';
	const OptionSetFieldAttribute = 'SetFieldAttribute';
	const OptionSetFormAttribute = 'SetFormAttribute';
	const OptionSetMethod = 'SetMethod';
	const OptionSetName = 'SetName';
	const OptionSetType = 'SetType';
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

		$text = "This options create a basic Forms Builder specification file.\n";
		$text.= "It can be use with '--module' to generate the specification inside certain module";
		$this->_options->addOption(Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), Option::TypeValue, $text, 'form-name'));

		$text = "This options remove a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), Option::TypeValue, $text, 'form-name'));

		$text = "This method sets the configuration for the attribute 'action' of a form.\n";
		$text.= "It must be use along with option '--value'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetAction, array('--set-action', '-sA'), Option::TypeValue, $text, 'form-name'));

		$text = "This method sets the configuration for the attribute 'method' of a form.\n";
		$text.= "It must be use along with option '--value'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetMethod, array('--set-method', '-sM'), Option::TypeValue, $text, 'form-name'));

		$text = "This method sets name of form (it doesn't change file names).\n";
		$text.= "It must be use along with option '--value'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetName, array('--set-name', '-sN'), Option::TypeValue, $text, 'form-name'));

		$text = "This method sets the form's type.\n";
		$text.= "It must be use along with option '--type'";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetType, array('--set-type', '-sT'), Option::TypeValue, $text, 'form-name'));

		$text = "This option appends a new field to a form specification.\n";
		$text.= "It requires options:\n";
		$text.= "\t'--name': Specifying a name for the field.\n";
		$text.= "\t'--type': Specifying a type for the field.\n";
		$text.= "Optional options:\n";
		$text.= "\t'--value': Specifying a default value.\n";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddField, array('--add-field', '-af'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddButton, array('--add-button', '-ab'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFormAttribute, array('--set-attribute', '-sfa'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFieldAttribute, array('--set-field-attribute', '-sa'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetButtonAttribute, array('--set-button-attribute', '-sba'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveAction, array('--remove-action', '-rA'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveMethod, array('--remove-method', '-rM'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveName, array('--remove-name', '-rN'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveField, array('--remove-field', '-rf'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButton, array('--remove-button', '-rb'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFormAttribute, array('--remove-attribute', '-ra'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFieldAttribute, array('--remove-field-attribute', '-rfa'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButtonAttribute, array('--remove-button-attribute', '-rba'), Option::TypeValue, $text, 'form-name'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionMode, array('--mode', '-m'), Option::TypeValue, $text, 'form-mode'));

		$text = "Some specific name required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionName, array('--name', '-n'), Option::TypeValue, $text, 'name'));

		$text = "Some specific value required by another option.";
		$this->_options->addOption(Option::EasyFactory(self::OptionValue, array('--value', '-v'), Option::TypeValue, $text, 'value'));

		$text = "Some specific type required by another option.\n";
		$text.= "When used with '--add-field' available options are:\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_INPUT."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_PASSWORD."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_TEXT."'\n";
		$text.= "\t- '".GC_FORMS_FIELDTYPE_ENUM."' (it should be used as: 'enum:VALUE1:VALUE2:OTHERVALUE')\n";
		$text.= "When used with '--add-button' available options are:\n";
		$text.= "\t- '".GC_FORMS_BOTTONTYPE_SUBMIT."'\n";
		$text.= "\t- '".GC_FORMS_BOTTONTYPE_RESET."'\n";
		$text.= "\t- '".GC_FORMS_BOTTONTYPE_BUTTON."' (default)\n";
		$text.= "When used with '--set-type' available options are:";
		foreach($Defaults[GC_DEFAULTS_FORMS_TYPES] as $type => $value) {
			$text.= "\n\t- '{$type}'";
		}
		$this->_options->addOption(Option::EasyFactory(self::OptionType, array('--type', '-t'), Option::TypeValue, $text, 'type'));

		$text = "Generate files inside a module.";
		$this->_options->addOption(Option::EasyFactory(self::OptionModule, array('--module', '-M'), Option::TypeValue, $text, 'module-name'));
	}
	protected function taskAddButton($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskAddField($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OptionAddField};
		//
		// Checking params.
		if(!$this->params->opt->{self::OptionName}) {
			$this->setError(self::ErrorWrongParameters, "No field name specified.");
		} elseif(!$this->params->opt->{self::OptionType}) {
			$this->setError(self::ErrorWrongParameters, "No field type specified.");
		} else {
			//
			// Loading parameters.
			$fieldName = $this->params->opt->{self::OptionName};
			$fieldType = $this->params->opt->{self::OptionType};
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
				$writer = new FormWriter($form);
				$writer->addField($fieldName, $fieldType);
				if(isset($this->params->opt->{self::OptionValue})) {
					$writer->setFieldDefault($fieldName, $this->params->opt->{self::OptionValue});
				}

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
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveButton($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveButtonAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveField($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveFieldAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveFormAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveMethod($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveName($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetAction($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OptionSetAction};
		$mode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$this->params->opt->{self::OptionValue}) {
			$this->setError(self::ErrorWrongParameters, "No value specified.");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$name}' action";
			if($mode) {
				echo " (for mode '{$mode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($name);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setAction($this->params->opt->{self::OptionValue}, $mode);

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
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetFieldAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetFormAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetMethod($spacer = "") {
		//
		// Default values.
		$name = $this->params->opt->{self::OptionSetMethod};
		$mode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$this->params->opt->{self::OptionValue}) {
			$this->setError(self::ErrorWrongParameters, "No value specified.");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$name}' method";
			if($mode) {
				echo " (for mode '{$mode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($name);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setMethod($this->params->opt->{self::OptionValue}, $mode);

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
		$mode = $this->params->opt->{self::OptionMode};
		//
		// Checking params.
		if(!$this->params->opt->{self::OptionValue}) {
			$this->setError(self::ErrorWrongParameters, "No value specified.");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$name}' name";
			if($mode) {
				echo " (for mode '{$mode}'): ";
			} else {
				echo ": ";
			}
			//
			// Loading form.
			$form = new Form($name);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setName($this->params->opt->{self::OptionValue}, $mode);

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
		$name = $this->params->opt->{self::OptionSetType};
		//
		// Checking params.
		if(!$this->params->opt->{self::OptionType}) {
			$this->setError(self::ErrorWrongParameters, "No type specified.");
		} else {
			//
			// Loading helpers.
			$this->loadHelpers();
			//
			// Removing form.
			echo "{$spacer}Setting form '{$name}' type: ";
			//
			// Loading form.
			$form = new Form($name);
			if(!$form->path()) {
				echo Color::Red('Failed').' (Error: '.Color::Yellow("There's no specification for this form").")\n";
			} else {
				$writer = new FormWriter($form);
				$writer->setType($this->params->opt->{self::OptionType});

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
