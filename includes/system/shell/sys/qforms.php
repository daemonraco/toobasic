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
 * @todo doc
 */
class QformsSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionAction = 'Action';
	const OptionBootstrapExtras = 'BootstrapExtras';
	const OptionButton = 'Button';
	const OptionCreate = 'Create';
	const OptionForced = 'Forced';
	const OptionField = 'Field';
	const OptionMethod = 'Method';
	const OptionModule = 'Module';
	const OptionRemove = 'Remove';
	const OptionType = 'Type';
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

		$this->_options->setHelpText("TODO tool summary");

		$text = "This option creates a Forms Builder specification file based on given parameters.\n";
		$text.= "It can be use with '--module' to generate the specification inside certain module";
		$this->_options->addOption(Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), Option::TypeValue, $text, 'form-name'));

		$text = "This option removes a Forms Builder specification file.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), Option::TypeValue, $text, 'form-name'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionField, array('--field', '-f'), Option::TypeMultiValue, $text, 'name-type'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionButton, array('--button', '-b'), Option::TypeMultiValue, $text, 'name-type'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionAction, array('--action', '-a'), Option::TypeValue, $text, 'action'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionMethod, array('--method', '-m'), Option::TypeValue, $text, 'method'));

		$text = "This option specifies a form type. Available values are:";
		foreach($Defaults[GC_DEFAULTS_FORMS_TYPES] as $type => $value) {
			$text.= "\n\t- '{$type}'";
		}
		$this->_options->addOption(Option::EasyFactory(self::OptionType, array('--type', '-t'), Option::TypeValue, $text, 'type'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionModule, array('--module', '-M'), Option::TypeValue, $text, 'name'));

		$text = "TODO help text";
		$this->_options->addOption(Option::EasyFactory(self::OptionForced, array('--forced', '-F'), Option::TypeNoValue, $text));

		$text = "This option adds some automatic twiks for bootstrap. Available values are:\n";
		$text = "\t- 'thin': thin inputs and green submits";
		$this->_options->addOption(Option::EasyFactory(self::OptionBootstrapExtras, array('--bootstrap-extras', '-bx'), Option::TypeValue, $text, 'type'));
	}
	protected function checkParameters() {
		$out = array(
			GC_AFIELD_STATUS => true,
			GC_AFIELD_ERROR => '',
			GC_AFIELD_TYPE => GC_FORMS_BUILDTYPE_BASIC,
			GC_AFIELD_ACTION => '#',
			GC_AFIELD_METHOD => 'get',
			GC_AFIELD_FIELDS => array(),
			GC_AFIELD_BUTTONS => array()
		);

		$fields = $this->params->opt->{self::OptionField};
		$buttons = $this->params->opt->{self::OptionButton};

		if($out[GC_AFIELD_STATUS]) {
			if($fields) {
				$fieldTypes = array(GC_FORMS_FIELDTYPE_HIDDEN, GC_FORMS_FIELDTYPE_INPUT, GC_FORMS_FIELDTYPE_PASSWORD, GC_FORMS_FIELDTYPE_TEXT, GC_FORMS_FIELDTYPE_ENUM);
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

					$out[GC_AFIELD_FIELDS][$fieldName] = array(
						GC_AFIELD_TYPE => $fieldType
					);
				}
			} else {
				$out[GC_AFIELD_STATUS] = false;
				$out[GC_AFIELD_ERROR] = 'No fields specified';
			}
		}

		if($out[GC_AFIELD_STATUS]) {
			if($buttons) {
				$buttonTypes = array(GC_FORMS_BUTTONTYPE_BUTTON, GC_FORMS_BUTTONTYPE_RESET, GC_FORMS_BUTTONTYPE_SUBMIT);
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

					$out[GC_AFIELD_BUTTONS][$buttonName] = array(
						GC_AFIELD_TYPE => $buttonType
					);
				}
			}
		}

		if($out[GC_AFIELD_STATUS]) {
			if(isset($this->params->opt->{self::OptionAction})) {
				$out[GC_AFIELD_STATUS] = $this->params->opt->{self::OptionAction};
			}
			if(isset($this->params->opt->{self::OptionMethod})) {
				$out[GC_AFIELD_METHOD] = $this->params->opt->{self::OptionMethod};
			}
			if(isset($this->params->opt->{self::OptionType})) {
				$out[GC_AFIELD_TYPE] = $this->params->opt->{self::OptionType};
			}
		}

		if($out[GC_AFIELD_STATUS] && isset($this->params->opt->{self::OptionBootstrapExtras})) {
			$twik = $this->params->opt->{self::OptionBootstrapExtras};

			switch($twik) {
				case 'thin':
					foreach($out[GC_AFIELD_FIELDS] as $k => $v) {
						$out[GC_AFIELD_FIELDS][$k]['attrs'] = array('class' => 'input-sm');
					}
					foreach($out[GC_AFIELD_BUTTONS] as $k => $v) {
						if($v[GC_AFIELD_TYPE] == GC_FORMS_BUTTONTYPE_SUBMIT) {
							$out[GC_AFIELD_BUTTONS][$k]['attrs'] = array('class' => 'btn-sm btn-success');
						} else {
							$out[GC_AFIELD_BUTTONS][$k]['attrs'] = array('class' => 'btn-sm btn-default');
						}
					}
					break;
				default:
					$out[GC_AFIELD_STATUS] = false;
					$out[GC_AFIELD_ERROR] = "Unknown bootstrap extra twik called '{$twik}'";
					break;
			}
		}

		return $out;
	}
	protected function taskCreate($spacer = "") {
		//
		// Default values.
		$formName = $this->params->opt->{self::OptionCreate};
		$module = $this->params->opt->{self::OptionModule};
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
			if(isset($this->params->opt->{self::OptionForced})) {
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
		$formName = $name ? $name : $this->params->opt->{self::OptionRemove};
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
