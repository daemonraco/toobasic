<?php

/**
 * @file Form.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

//
// Class aliases.
use JSONValidator;
use TooBasic\MagicProp;
use TooBasic\Managers\RoutesManager;
use TooBasic\Names;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class Form
 * This class represents a form configuration and the logic to trigger its build.
 */
class Form {
	//
	// Protected properties.
	/**
	 * @var \stdClass Current form loaded configuration.
	 */
	protected $_config = false;
	/**
	 * @var string Current form name.
	 */
	protected $_name = false;
	/**
	 * @var string Current form configuration file path.
	 */
	protected $_path = false;
	/**
	 * @var boolean Current form status. It usually changes after loading.
	 */
	protected $_status = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $name Name to assign to the created form representation.
	 */
	public function __construct($name) {
		//
		// Saving name.
		$this->_name = $name;
		//
		// Global dependencies.
		global $Paths;
		//
		// Guessing names.
		$fileName = Names::SnakeFilename($this->name());
		$this->_path = Paths::Instance()->customPaths($Paths[GC_PATHS_FORMS], $fileName, Paths::ExtensionJSON);
	}
	//
	// Public methods.
	/**
	 * This method returns the proper form action based on its defaults and
	 * specific values for certain mode.
	 *
	 * @param string $mode Mode to be used when checking action.
	 * @return string Returns a URL.
	 */
	public function action($mode = false) {
		//
		// Loading all required settings.
		$this->load();
		//
		// Default value.
		$action = $this->_config->form->action;
		//
		// Current mode's value.
		if($mode && isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->action)) {
			$action = $this->_config->form->modes->{$mode}->action;
		}
		//
		// Cleaning routes and returning.
		return RoutesManager::Instance()->enroute($action);
	}
	/**
	 * This method returns a proper list of form's HTML attributes merging
	 * defaults and specific attributes for certain mode.
	 *
	 * @param string $mode Mode to be used when merging attributes.
	 * @return \stdClass Returns a complete list of attributes.
	 */
	public function attributes($mode = false) {
		//
		// Default values.
		$out = new \stdClass();
		//
		// Copying default values.
		foreach($this->_config->form->attrs as $k => $v) {
			$out->{$k} = $v;
		}
		if($mode) {
			//
			// Copying mode's form attributies, if any.
			if(isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->attrs)) {
				foreach($this->_config->form->modes->{$mode}->attrs as $k => $v) {
					$out->{$k} = $v;
				}
			}
		}

		return $out;
	}
	/**
	 * This method generates an HTML form based on this form's configuration
	 * using the proper builder.
	 *
	 * @param mixed[string] $item Information to fill fields (except for mode
	 * 'create').
	 * @param string $mode Mode in which it must be built.
	 * @param mixed[string] $flags List of extra parameters used to build.
	 * @return string Returns a HTML piece of code.
	 * @throws \TooBasic\Forms\FormsException
	 */
	public function buildFor($item, $mode = false, $flags = []) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Loading all required settings.
		$this->load();
		//
		// Creating the proper builder.
		$builder = false;
		if(isset($Defaults[GC_DEFAULTS_FORMS_TYPES][$this->_config->form->type])) {
			$className = $Defaults[GC_DEFAULTS_FORMS_TYPES][$this->_config->form->type];
			$builder = new $className($this);
		} else {
			throw new FormsException(Translate::Instance()->EX_unknown_type_at_path_on_form([
				'type' => $this->_config->form->type,
				'path' => '/form/type',
				'form' => $this->name()
			]));
		}
		//
		// Fixing mode.
		if(!$mode) {
			$mode = GC_FORMS_BUILDMODE_EDIT;
		}
		//
		// Building form.
		return $builder->buildFor($item, $mode, $flags);
	}
	/**
	 * This method provides access to the list of attributes of certain
	 * button.
	 *
	 * @param string $buttonName Name of the button to look for.
	 * @param string $mode Looks for in the specifics of certain mode.
	 * @return \stdClass Returns a list of attributes with their values.
	 */
	public function buttonAttributes($buttonName, $mode = false) {
		$config = $this->buttonConfigFor($buttonName, $mode);
		return $config->attrs;
	}
	/**
	 * This method provides access to the configuration of certain button.
	 *
	 * @param string $buttonName Name of the button to look for.
	 * @param string $mode Looks for in the specifics of certain mode.
	 * @return \stdClass Returns a configuration object.
	 */
	public function buttonConfigFor($buttonName, $mode = false) {
		//
		// Loading all required settings.
		$this->load();
		//
		// Default values.
		$out = false;
		//
		// Checking mode's buttons.
		if($mode && isset($this->_config->form->modes->{$mode}->buttons->{$buttonName})) {
			$out = $this->_config->form->modes->{$mode}->buttons->{$buttonName};
		} else {
			if(isset($this->_config->form->buttons->{$buttonName})) {
				$out = $this->_config->form->buttons->{$buttonName};
			} else {
				$out = new \stdClass();
			}
		}

		return $out;
	}
	/**
	 * This method generates a proper button id for certain name.
	 *
	 * @param string $buttonName Name to use when generating.
	 * @return string Returns a proper button id.
	 */
	public function buttonId($buttonName) {
		$name = $this->virtualName();
		return $name ? "{$name}_{$buttonName}" : $buttonName;
	}
	/**
	 * This method provides access to the label of certain button.
	 *
	 * @param string $buttonName Name of the button to look for.
	 * @param string $mode Looks for in the specifics of certain mode.
	 * @return string Returns a buttons label.
	 */
	public function buttonLabel($buttonName, $mode = false) {
		$config = $this->buttonConfigFor($buttonName, $mode);
		return $config->label;
	}
	/**
	 * This method returns the proper list of buttons based on current form
	 * defaults and specific buttons for certain mode.
	 *
	 * @param string $mode Mode to be used when checking buttons.
	 * @return \stdClass Returns a list of buttons.
	 */
	public function buttonsFor($mode = false) {
		//
		// Loading all required settings.
		$this->load();
		//
		// Default values.
		$out = $this->_config->form->buttons;
		//
		// Checking mode's buttons.
		if($mode && isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->buttons)) {
			$out = $this->_config->form->modes->{$mode}->buttons;
		}

		return array_keys(get_object_vars($out));
	}
	/**
	 * This method provides access to the type of certain button.
	 *
	 * @param string $buttonName Name of the button to look for.
	 * @param string $mode Looks for in the specifics of certain mode.
	 * @return string Returns a buttons type.
	 */
	public function buttonType($buttonName, $mode = false) {
		$config = $this->buttonConfigFor($buttonName, $mode);
		return $config->type;
	}
	/**
	 * This method provides access to the loaded configuration.
	 *
	 * @return \stdClass Returns a configuration object.
	 */
	public function config() {
		$this->load();
		return $this->_config;
	}
	/**
	 * This method provides access to the list of attributes of certain field.
	 *
	 * @param type $fieldName Name of the field to look for.
	 * @return \stdClass Returns a field attributes list.
	 */
	public function fieldAttributes($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->attrs : false;
	}
	/**
	 * This method provides access to certain field's configuration for the
	 * empty option. This applies only to 'enum' fieds.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return \stdClass Returns a field attributes list.
	 */
	public function fieldEmptyOption($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && isset($this->_config->form->fields->{$fieldName}->emptyOption) ? $this->_config->form->fields->{$fieldName}->emptyOption : false;
	}
	/**
	 * This method provides access to certain field's list of modes in which
	 * it does not appear.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string[] Returns a field attributes list.
	 */
	public function fieldExcludedModes($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && isset($this->_config->form->fields->{$fieldName}->excludedModes) ? $this->_config->form->fields->{$fieldName}->excludedModes : [];
	}
	/**
	 * This method provides access to certain field's proper id.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string Returns an id.
	 */
	public function fieldId($fieldName) {
		$name = $this->virtualName();
		return $name ? "{$name}_{$fieldName}" : $fieldName;
	}
	/**
	 * This method provides access to certain field's proper label translation
	 * key.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string Returns a translation key.
	 */
	public function fieldLabel($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->label : '';
	}
	/**
	 * This method provides access to list of field names.
	 *
	 * @return string[] Returns a list of names.
	 */
	public function fields() {
		$this->load();
		return array_keys(get_object_vars($this->_config->form->fields));
	}
	/**
	 * This method provides access to certain field's type.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string Returns a type name.
	 */
	public function fieldType($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->type : false;
	}
	/**
	 * This method provides access to certain field's default value.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string Returns a default value.
	 */
	public function fieldValue($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->value : '';
	}
	/**
	 * This method provides access to certain field's list of value. This
	 * method will always return an empty array unless it's an 'enum' field.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @return string[] Returns a list of value.
	 */
	public function fieldValues($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && $this->_config->form->fields->{$fieldName}->type == GC_FORMS_FIELDTYPE_ENUM ? $this->_config->form->fields->{$fieldName}->values : [];
	}
	/**
	 * This method provides access to a proper id for this form.
	 *
	 * @return string Returns an id.
	 */
	public function id() {
		return $this->virtualName();
	}
	/**
	 * This method allow to know if some field should not be shown in certain
	 * mode.
	 *
	 * @param string $fieldName Name of the field to look for.
	 * @param string $mode Mode in to chec.
	 * @return boolean Returns TRUE when the field should not be shown.
	 */
	public function isFieldExcluded($fieldName, $mode) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && isset($this->_config->form->fields->{$fieldName}->excludedModes) && in_array($mode, $this->_config->form->fields->{$fieldName}->excludedModes);
	}
	/**
	 * This method allows to know if current form is in read-only mode for
	 * certain mode.
	 *
	 * @param string $mode Mode to be used when checking read-only status.
	 * @return boolean Returns TRUE when it's a read-only form.
	 */
	public function isReadOnly($mode = false) {
		$out = $this->_config->form->readonly || $mode == GC_FORMS_BUILDMODE_VIEW || $mode == GC_FORMS_BUILDMODE_REMOVE;
		//
		// Checking if the mode is configured to be readonly.
		if($mode && !$out && isset($this->_config->form->modes->{$mode}->readonly)) {
			$out = $this->_config->form->modes->{$mode}->readonly;
		}

		return $out;
	}
	/**
	 * This method provides access to the MagicProp singleton.
	 *
	 * @return \TooBasic\MagicProp returns a singleton pointer.
	 */
	public function magic() {
		static $magic = false;

		if($magic === false) {
			$magic = MagicProp::Instance();
		}

		return $magic;
	}
	/**
	 * This method returns the proper form method based on its defaults and
	 * specific values for certain mode.
	 *
	 * @param string $mode Mode to be used when checking mode.
	 * @return string Returns a method name.
	 */
	public function method($mode = false) {
		//
		// Loading all required settings.
		$this->load();
		//
		// Default value.
		$out = $this->_config->form->method;
		//
		// Mode specific value.
		if($mode && isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->method)) {
			$out = $this->_config->form->modes->{$mode}->method;
		}

		return $out;
	}
	/**
	 * This method returns a list of configured modes.
	 *
	 * @return string[] Returns a list of names.
	 */
	public function modes() {
		//
		// Loading all required settings.
		$this->load();

		return isset($this->_config->form->modes) ? array_keys(get_object_vars($this->_config->form->modes)) : [];
	}
	/**
	 * This method provides access to this form's name.
	 *
	 * @return string Returns a name.
	 */
	public function name() {
		return $this->_name;
	}
	/**
	 * This method provides access to this form's configuration file's path.
	 *
	 * @return string Returns an absolute path.
	 */
	public function path() {
		return $this->_path;
	}
	/**
	 * This method provides access to this form's current status.
	 *
	 * @return boolean Returns TRUE when it is correctly loaded.
	 */
	public function status() {
		$this->load();
		return $this->_status;
	}
	/**
	 * This method provides access to current form's type.
	 *
	 * @return string Returns a type name.
	 */
	public function type() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Loading all required settings.
		$this->load();

		return isset($this->_config->form->type) ? $this->_config->form->type : $Defaults[GC_DEFAULTS_FORMS_TYPE];
	}
	/**
	 * This method returns the name set for current form base on its
	 * configuration.
	 *
	 * @return string Returns a name.
	 */
	public function virtualName() {
		//
		// Loading all required settings.
		$this->load();

		return $this->_config->form->name;
	}
	//
	// Protected methods.
	/**
	 * This method checks for required fields in a form configuration and
	 * expand the rest with default values, in other words, it sanitizes the
	 * configuration.
	 *
	 * @throws \TooBasic\Forms\FormsException
	 */
	protected function checkConfig() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking required fields.
		if(!count(get_object_vars($this->_config->form->fields))) {
			throw new FormsException(Translate::Instance()->EX_unable_to_find_path_or_empty_on_form([
				'path' => '/form/fields',
				'form' => $this->name()
			]));
		}
		//
		// Checking and expanding default form values.
		$formFields = [
			'type' => $Defaults[GC_DEFAULTS_FORMS_TYPE],
			'name' => '',
			'action' => '#',
			'method' => 'get',
			'attrs' => false,
			'modes' => false,
			'buttons' => false,
			'readonly' => false
		];
		$this->_config->form = \TooBasic\objectCopyAndEnforce(array_keys($formFields), new \stdClass(), $this->_config->form, $formFields);
		//
		// Required form objects.
		foreach(['attrs', 'modes', 'buttons'] as $name) {
			if(!$this->_config->form->{$name}) {
				$this->_config->form->{$name} = new \stdClass();
			}
		}
		//
		// Checking and expanding default field values.
		$fieldFields = [
			'type' => 'input',
			'attrs' => false,
			'value' => '',
			'label' => false,
			'values' => [],
			'excludedModes' => []
		];
		foreach($this->_config->form->fields as $name => $config) {
			//
			// Expanding the simple configuration.
			if(!is_object($config)) {
				$aux = new \stdClass();
				$aux->type = $config;
				$config = $aux;
			}
			//
			// Enforcing fields.
			$config = \TooBasic\objectCopyAndEnforce(array_keys($fieldFields), new \stdClass(), $config, $fieldFields);
			//
			// Field label.
			$config->label = !$config->label ? "label_formcontrol_{$name}" : $config->label;
			//
			// Required objects.
			foreach(['attrs'] as $subName) {
				if(!$config->{$subName}) {
					$config->{$subName} = new \stdClass();
				}
			}
			//
			// Special checks for 'enum' fields.
			if($config->type == GC_FORMS_FIELDTYPE_ENUM) {
				//
				// Checking options.
				if(!boolval($config->values)) {
					throw new FormsException(Translate::Instance()->EX_type_at_path_has_no_values_on_form([
						'type' => GC_FORMS_FIELDTYPE_ENUM,
						'path' => "/form/fields/{$name}",
						'form' => $this->name()
					]));
				}
				//
				// Checking empty option configuration.
				if(isset($config->emptyOption)) {
					if(!is_object($config->emptyOption)) {
						$aux = new \stdClass();
						$aux->label = $config->emptyOption;
						$config->emptyOption = $aux;
					}
					if(!isset($config->emptyOption->label)) {
						$config->emptyOption->label = 'select_option_NOOPTION';
					}
					if(!isset($config->emptyOption->value)) {
						$config->emptyOption->value = '';
					}
				}
			}
			//
			// Updating configuration.
			$this->_config->form->fields->{$name} = $config;
		}
		//
		// Checking buttons.
		$this->checkConfigForButtons($this->_config->form->buttons);
		foreach($this->_config->form->modes as $mode => $config) {
			if(isset($config->buttons)) {
				$this->checkConfigForButtons($this->_config->form->modes->{$mode}->buttons);
			}
		}
		//
		// Debugging configuration.
		if(isset($this->magic()->params->debugformconfig)) {
			\TooBasic\debugThing($this->_config);
		}
	}
	/**
	 * This method sanitizes a list of buttons configuration.
	 *
	 * @param \stdClass $buttons List of buttons.
	 */
	protected function checkConfigForButtons(&$buttons) {
		//
		// Checking buttons configuration.
		$buttonFields = [
			'type' => 'button',
			'attrs' => false,
			'label' => false
		];
		foreach($buttons as $name => $config) {
			//
			// Expanding the simple configuration.
			if(!is_object($config)) {
				$aux = new \stdClass();
				$aux->type = $config;
				$config = $aux;
			}
			//
			// Enforcing fields.
			$config = \TooBasic\objectCopyAndEnforce(array_keys($buttonFields), new \stdClass(), $config, $buttonFields);
			//
			// Botton label.
			$config->label = $config->label ? $config->label : "btn_{$name}";
			//
			// Required objects.
			foreach(['attrs'] as $subName) {
				if(!$config->{$subName}) {
					$config->{$subName} = new \stdClass();
				}
			}
			//
			// Updating configuration.
			$buttons->{$name} = $config;
		}
	}
	/**
	 * This method loads current form's configuration.
	 */
	protected function load() {
		//
		// Avoiding multipe loads of configurations.
		if($this->_config === false) {
			//
			// Reseting current stauts value.
			$this->_status = false;
			//
			// Checking path existence.
			if($this->path() && is_readable($this->path())) {
				//
				// Loading configuration contents.
				$jsonString = file_get_contents($this->path());
				//
				// Validating JSON strucutre.
				if(!self::GetValidator()->validate($jsonString, $info)) {
					throw new FormsException(Translate::Instance()->EX_json_path_fail_specs(['path' => $this->path()])." {$info[JV_FIELD_ERROR][JV_FIELD_MESSAGE]}");
				}
				//
				// Loading configuration.
				$this->_config = json_decode($jsonString);
				//
				// Checking configuration.
				if($this->_config) {
					$this->_status = true;
					$this->checkConfig();
				}
			} elseif(!$this->path()) {
				throw new FormsException(Translate::Instance()->EX_unknown_form(['name' => $this->name()]));
			} else {
				throw new FormsException(Translate::Instance()->EX_unable_to_read_form_path(['path' => $this->path()]));
			}
		}
	}
	//
	// Protected class methods.
	/**
	 * This class method provides access to a JSONValidator object loaded for
	 * Form Builder specifications.
	 *
	 * @return \JSONValidator Returns a loaded validator.
	 */
	protected static function GetValidator() {
		//
		// Validators cache.
		static $validator = false;
		//
		// Checking if the validators is loaded.
		if(!$validator) {
			global $Directories;
			$validator = JSONValidator::LoadFromFile("{$Directories[GC_DIRECTORIES_SPECS]}/formbuilder.json");
		}

		return $validator;
	}
}
