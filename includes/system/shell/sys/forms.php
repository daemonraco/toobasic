<?php

/**
 * @file forms.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Forms\FormsManager;
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
	const OptionRemove = 'Remove';
	const OptionRemoveAction = 'RemoveAction';
	const OptionRemoveButton = 'RemoveButton';
	const OptionRemoveButtonAttribute = 'RemoveButtonAttribute';
	const OptionRemoveField = 'RemoveField';
	const OptionRemoveFieldAttribute = 'RemoveFieldAttribute';
	const OptionRemoveFormAttribute = 'RemoveFormAttribute';
	const OptionRemoveMethod = 'RemoveMethod';
	const OptionSetButtonAttribute = 'SetButtonAttribute';
	const OptionSetFieldAttribute = 'SetFieldAttribute';
	const OptionSetFormAttribute = 'SetFormAttribute';
	const OptionSetAction = 'SetAction';
	const OptionSetMethod = 'SetMethod';
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
		$this->_options->setHelpText("This tool allows you to create, modify and remove Forms Builder specification files.");

		$text = "This options create a basic Forms Builder specification file.\n";
		$text.= "It can be use with '--module' to generate the specification inside certain module";
		$this->_options->addOption(Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), Option::TypeValue, $text, 'value'));

		$text = "This options remove a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetAction, array('--set-action', '-sA'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetMethod, array('--set-method', '-sM'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddField, array('--add-field', '-af'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionAddButton, array('--add-button', '-ab'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFormAttribute, array('--set-attribute', '-sfa'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetFieldAttribute, array('--set-field-attribute', '-sa'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSetButtonAttribute, array('--set-button-attribute', '-sba'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveAction, array('--remove-action', '-rA'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveMethod, array('--remove-method', '-rM'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveField, array('--remove-field', '-rf'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButton, array('--remove-button', '-rb'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFormAttribute, array('--remove-attribute', '-ra'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveFieldAttribute, array('--remove-field-attribute', '-rfa'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemoveButtonAttribute, array('--remove-button-attribute', '-rba'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionMode, array('--mode', '-m'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionValue, array('--value', '-v'), Option::TypeValue, $text, 'value'));

		$text = "@TODO code it.";
		$this->_options->addOption(Option::EasyFactory(self::OptionType, array('--type', '-t'), Option::TypeValue, $text, 'value'));

		$text = "Generate files inside a module.";
		$this->_options->addOption(Option::EasyFactory(self::OptionModule, array('--module', '-M'), Option::TypeValue, $text, 'value'));
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
	protected function taskAddField($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskAddButton($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetFormAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetFieldAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetButtonAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
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
	protected function taskRemoveField($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveButton($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveFormAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveFieldAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveButtonAttribute($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetAction($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskSetMethod($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveAction($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
	protected function taskRemoveMethod($spacer = "") {
		debugit("TODO write some valid code for this option.", true);
	}
}
