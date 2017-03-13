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
 * @class QformsSystool
 * This system shell tool provides a quick mechanism to generate form
 * specifications.
 */
class QformsSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OPTION_ACTION = 'Action';
	const OPTION_BOOTSTRAP_EXTRAS = 'BootstrapExtras';
	const OPTION_BUTTON = 'Button';
	const OPTION_CREATE = 'Create';
	const OPTION_FORCED = 'Forced';
	const OPTION_FIELD = 'Field';
	const OPTION_METHOD = 'Method';
	const OPTION_MODULE = 'Module';
	const OPTION_REMOVE = 'Remove';
	const OPTION_TYPE = 'Type';
	const TWIK_BCOLORS = 'bcolors';
	const TWIK_THIN = 'thin';
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

		$this->_options->setHelpText("This is an alternative to sys-tool 'forms' and it allows to create a complete basic form in one command.");

		$text = "This option creates a Forms Builder specification file based on given parameters.\n";
		$text.= "It can be use with '--module' to generate the specification inside certain module.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_CREATE, ['create', 'new', 'add'], Option::TypeValue, $text, 'form-name'));

		$text = "This option removes a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE, ['remove', 'rm', 'delete'], Option::TypeValue, $text, 'form-name'));

		$text = "This option allows to specify a field. ";
		$text.= "Its value must be a string separated by colons (':') where each piece is:\n";
		$text.= "\t- 1st: Field name.\n";
		$text.= "\t- 2nd: Field type.\n";
		$text.= "\t- 3rd: Extra values.\n";
		$text.= "When the type is '".GC_FORMS_FIELDTYPE_ENUM."', the 3rd piece must be a list of values also separater by colons.\n";
		$text.= "Available types are:";
		$text.= "\n\t- '".GC_FORMS_FIELDTYPE_ENUM."'.";
		$text.= "\n\t- '".GC_FORMS_FIELDTYPE_HIDDEN."'.";
		$text.= "\n\t- '".GC_FORMS_FIELDTYPE_INPUT."'.";
		$text.= "\n\t- '".GC_FORMS_FIELDTYPE_PASSWORD."'.";
		$text.= "\n\t- '".GC_FORMS_FIELDTYPE_TEXT."'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FIELD, ['--field', '-f'], Option::TypeMultiValue, $text, 'name:type:...'));

		$text = "This option allows to specify a button. ";
		$text.= "Its value must be a string separated by colons (':') where each piece is:\n";
		$text.= "\t- 1st: Button name.\n";
		$text.= "\t- 2nd: Button type.\n";
		$text.= "Available types are:";
		$text.= "\n\t- '".GC_FORMS_BUTTONTYPE_BUTTON."'.";
		$text.= "\n\t- '".GC_FORMS_BUTTONTYPE_SUBMIT."'.";
		$text.= "\n\t- '".GC_FORMS_BUTTONTYPE_RESET."'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_BUTTON, ['--button', '-b'], Option::TypeMultiValue, $text, 'name:type'));

		$text = "This option specifies the default URL where a form should submit its data.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_ACTION, ['--action', '-a'], Option::TypeValue, $text, 'action'));

		$text = "This option specifies which method should be used when the form is submitted.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_METHOD, ['--method', '-m'], Option::TypeValue, $text, 'method'));

		$text = "This option specifies a form type. Available values are:";
		foreach(array_keys($Defaults[GC_DEFAULTS_FORMS_TYPES]) as $type) {
			$text.= "\n\t- '{$type}'";
		}
		$this->_options->addOption(Option::EasyFactory(self::OPTION_TYPE, ['--type', '-t'], Option::TypeValue, $text, 'type'));

		$text = "Generate files inside a module.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_MODULE, ['--module', '-M'], Option::TypeValue, $text, 'name'));

		$text = "This option forces the creation of this form previously removing other definition sharing the same name.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FORCED, ['--forced', '-F'], Option::TypeNoValue, $text));

		$text = "This option adds some automatic twiks for bootstrap. Available values are:";
		$text.= "\n\t- 'thin': thin inputs.";
		$text.= "\n\t- 'bcolors': Green submits and default for other buttons";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_BOOTSTRAP_EXTRAS, ['--bootstrap-extras', '-bx'], Option::TypeMultiValue, $text, 'twik'));
	}
	protected function checkParameters() {
		$out = [
			GC_AFIELD_STATUS => true,
			GC_AFIELD_ERROR => '',
			GC_AFIELD_TYPE => GC_FORMS_BUILDTYPE_BASIC,
			GC_AFIELD_ACTION => '#',
			GC_AFIELD_METHOD => 'get',
			GC_AFIELD_FIELDS => [],
			GC_AFIELD_BUTTONS => []
		];

		$fields = $this->params->opt->{self::OPTION_FIELD};
		$buttons = $this->params->opt->{self::OPTION_BUTTON};

		if($out[GC_AFIELD_STATUS]) {
			if($fields) {
				$fieldTypes = [GC_FORMS_FIELDTYPE_HIDDEN, GC_FORMS_FIELDTYPE_INPUT, GC_FORMS_FIELDTYPE_PASSWORD, GC_FORMS_FIELDTYPE_TEXT, GC_FORMS_FIELDTYPE_ENUM];
				foreach($fields as $field) {
					$fieldParts = explode(':', str_replace(',', '_', $field));
					$fieldName = array_shift($fieldParts);
					$fieldType = array_shift($fieldParts);
					if(!$fieldType) {
						$out[GC_AFIELD_STATUS] = false;
						$out[GC_AFIELD_ERROR] = "Field '{$fieldName}' has no type specification";
						break;
					}
					if(!in_array($fieldType, $fieldTypes)) {
						$out[GC_AFIELD_STATUS] = false;
						$out[GC_AFIELD_ERROR] = "Unkwnon type '{$fieldType}' for field '{$fieldName}'";
						break;
					}
					if($fieldType == GC_FORMS_FIELDTYPE_ENUM) {
						if(!$fieldParts) {
							$out[GC_AFIELD_STATUS] = false;
							$out[GC_AFIELD_ERROR] = "Enumerative field '{$fieldName}' requires values";
							break;
						} else {
							$fieldType = "{$fieldType}:".implode(':', $fieldParts);
						}
					}

					$out[GC_AFIELD_FIELDS][$fieldName] = [
						GC_AFIELD_TYPE => $fieldType
					];
				}
			} else {
				$out[GC_AFIELD_STATUS] = false;
				$out[GC_AFIELD_ERROR] = 'No fields specified';
			}
		}

		if($out[GC_AFIELD_STATUS]) {
			if($buttons) {
				$buttonTypes = [GC_FORMS_BUTTONTYPE_BUTTON, GC_FORMS_BUTTONTYPE_RESET, GC_FORMS_BUTTONTYPE_SUBMIT];
				foreach($buttons as $button) {
					$buttonParts = explode(':', str_replace(',', '_', $button));
					$buttonName = array_shift($buttonParts);
					$buttonType = array_shift($buttonParts);
					if(!$buttonType) {
						$out[GC_AFIELD_STATUS] = false;
						$out[GC_AFIELD_ERROR] = "Button '{$buttonName}' has no type specification";
						break;
					}
					if(!in_array($buttonType, $buttonTypes)) {
						$out[GC_AFIELD_STATUS] = false;
						$out[GC_AFIELD_ERROR] = "Unkwnon type '{$buttonType}' for button '{$buttonName}'";
						break;
					}

					$out[GC_AFIELD_BUTTONS][$buttonName] = [
						GC_AFIELD_TYPE => $buttonType
					];
				}
			}
		}

		if($out[GC_AFIELD_STATUS]) {
			if(isset($this->params->opt->{self::OPTION_ACTION})) {
				$out[GC_AFIELD_ACTION] = $this->params->opt->{self::OPTION_ACTION};
			}
			if(isset($this->params->opt->{self::OPTION_METHOD})) {
				$out[GC_AFIELD_METHOD] = $this->params->opt->{self::OPTION_METHOD};
			}
			if(isset($this->params->opt->{self::OPTION_TYPE})) {
				$out[GC_AFIELD_TYPE] = $this->params->opt->{self::OPTION_TYPE};
			}
		}

		if($out[GC_AFIELD_STATUS] && isset($this->params->opt->{self::OPTION_BOOTSTRAP_EXTRAS})) {
			foreach($out[GC_AFIELD_FIELDS] as $k => $v) {
				if(!isset($out[GC_AFIELD_FIELDS][$k]['attrs'])) {
					$out[GC_AFIELD_FIELDS][$k]['attrs'] = ['class' => ''];
				}
			}
			foreach($out[GC_AFIELD_BUTTONS] as $k => $v) {
				if(!isset($out[GC_AFIELD_BUTTONS][$k]['attrs'])) {
					$out[GC_AFIELD_BUTTONS][$k]['attrs'] = ['class' => ''];
				}
			}

			foreach($this->params->opt->{self::OPTION_BOOTSTRAP_EXTRAS} as $twik) {
				switch($twik) {
					case self::TWIK_THIN:
						foreach($out[GC_AFIELD_FIELDS] as $k => $v) {
							$out[GC_AFIELD_FIELDS][$k]['attrs']['class'].= ' input-sm';
						}
						foreach($out[GC_AFIELD_BUTTONS] as $k => $v) {
							$out[GC_AFIELD_BUTTONS][$k]['attrs']['class'].= ' btn-sm';
						}
						break;
					case self::TWIK_BCOLORS:
						foreach($out[GC_AFIELD_BUTTONS] as $k => $v) {
							if($v[GC_AFIELD_TYPE] == GC_FORMS_BUTTONTYPE_SUBMIT) {
								$out[GC_AFIELD_BUTTONS][$k]['attrs']['class'].= ' btn-success';
							} else {
								$out[GC_AFIELD_BUTTONS][$k]['attrs']['class'].= ' btn-default';
							}
						}
						break;
					default:
						$out[GC_AFIELD_STATUS] = false;
						$out[GC_AFIELD_ERROR] = "Unknown bootstrap extra twik called '{$twik}'";
						break;
				}
			}
			//
			// Trimming and removing empty classes.
			foreach($out[GC_AFIELD_FIELDS] as $k => $v) {
				$out[GC_AFIELD_FIELDS][$k]['attrs']['class'] = trim($out[GC_AFIELD_FIELDS][$k]['attrs']['class']);
				if(!$out[GC_AFIELD_FIELDS][$k]['attrs']['class']) {
					unset($out[GC_AFIELD_FIELDS][$k]['attrs']['class']);
				}
			}
			foreach($out[GC_AFIELD_BUTTONS] as $k => $v) {
				$out[GC_AFIELD_BUTTONS][$k]['attrs']['class'] = trim($out[GC_AFIELD_BUTTONS][$k]['attrs']['class']);
				if(!$out[GC_AFIELD_BUTTONS][$k]['attrs']['class']) {
					unset($out[GC_AFIELD_BUTTONS][$k]['attrs']['class']);
				}
			}
		}

		return $out;
	}
	protected function taskCreate($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OPTION_CREATE};
		$module = $this->params->opt->{self::OPTION_MODULE};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Checking parameters.
		$paramsResult = $this->checkParameters();
		if($paramsResult[GC_AFIELD_STATUS]) {
			//
			// Default values.
			$ok = true;
			$error = false;
			$form = false;
			$writer = false;
			//
			// Checking forced parameter.
			if(isset($this->params->opt->{self::OPTION_FORCED})) {
				$this->taskRemove($spacer, $formName);
			}
			//
			// Creating form.
			echo "{$spacer}Creating form '{$formName}': ";
			$creationResult = $this->_formsHelper->createForm($formName, $module);
			if($creationResult[GC_AFIELD_STATUS]) {
				echo Color::Green('Done')." (Path: {$creationResult[GC_AFIELD_PATH]})\n";
			} else {
				echo Color::Red('Failed').' (Error: '.Color::Yellow($creationResult[GC_AFIELD_ERROR]).")\n";
				$ok = false;
			}

			if($ok) {
				$form = new Form($formName);
				$writer = new FormWriter($form);
			}

			if($ok) {
				echo "{$spacer}\tSetting basic form values:\n";

				echo "{$spacer}\t\t- Action '{$paramsResult[GC_AFIELD_ACTION]}': ";
				$writer->setAction($paramsResult[GC_AFIELD_ACTION]);
				echo Color::Green("Done\n");

				echo "{$spacer}\t\t- Method '{$paramsResult[GC_AFIELD_METHOD]}': ";
				$writer->setMethod($paramsResult[GC_AFIELD_METHOD]);
				echo Color::Green("Done\n");

				echo "{$spacer}\t\t- Type '{$paramsResult[GC_AFIELD_TYPE]}': ";
				$writer->setType($paramsResult[GC_AFIELD_TYPE]);
				echo Color::Green("Done\n");
			}

			if($ok) {
				echo "{$spacer}\tAdding fields:\n";
				foreach($paramsResult[GC_AFIELD_FIELDS] as $fieldName => $config) {
					echo "{$spacer}\t\t- '{$fieldName}': ";
					$writer->addField($fieldName, $config[GC_AFIELD_TYPE], $error);
					if(!$error) {
						if(isset($config['attrs'])) {
							foreach($config['attrs'] as $k => $v) {
								$writer->setFieldAttribute($fieldName, $k, $v);
							}
						}

						echo Color::Green("Done\n");
					} else {
						echo Color::Red('Done').' (Error: '.Color::Yellow($error).")\n";
					}
				}
			}

			if($ok) {
				echo "{$spacer}\tAdding buttons:\n";
				foreach($paramsResult[GC_AFIELD_BUTTONS] as $buttonName => $config) {
					echo "{$spacer}\t\t- '{$buttonName}': ";
					$writer->addButton($buttonName, $config[GC_AFIELD_TYPE], false, $error);
					if(!$error) {
						if(isset($config['attrs'])) {
							foreach($config['attrs'] as $k => $v) {
								$writer->setButtonAttribute($buttonName, $k, $v);
							}
						}

						echo Color::Green("Done\n");
					} else {
						echo Color::Red('Done').' (Error: '.Color::Yellow($error).")\n";
					}
				}
			}

			if($ok) {
				$writer->save();
			}
		} else {
			$this->setError(self::ErrorWrongParameters, $paramsResult[GC_AFIELD_ERROR]);
		}
	}
	protected function taskRemove($spacer = "", $name = false) {
		//
		// Default values.
		$formName = $name ? $name : $this->params->opt->{self::OPTION_REMOVE};
		//
		// Loading helpers.
		$this->loadHelpers();
		//
		// Removing form.
		echo "{$spacer}Removing form '{$formName}': ";
		$result = $this->_formsHelper->removeForm($formName);
		if($result[GC_AFIELD_STATUS]) {
			echo Color::Green('Done')." (Path: {$result[GC_AFIELD_PATH]})\n";
		} else {
			echo Color::Red('Failed').' (Error: '.Color::Yellow($result[GC_AFIELD_ERROR]).")\n";
		}
	}
}
