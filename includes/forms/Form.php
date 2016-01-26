<?php

/**
 * @file Form.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

//
// Class aliases.
use TooBasic\MagicProp;
use TooBasic\Managers\RoutesManager;
use TooBasic\Names;
use TooBasic\Paths;

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
	 * @var \TooBasic\MagicProp MagicProp shortcut.
	 */
	protected $_magic = false;
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
	public function buildFor($item, $mode = false, $flags = array()) {
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
			throw new FormsException("Unknown form type '{$this->_config->form->type}' at path '///form/type'.");
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
	public function buttonAttributes($buttonName, $mode = false) {
		$config = $this->buttonConfigFor($buttonName, $mode);
		return $config->attrs;
	}
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
	public function buttonId($buttonName) {
		$name = $this->virtualName();
		return $name ? "{$name}_{$buttonName}" : $buttonName;
	}
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
	public function fieldAttributes($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->attrs : false;
	}
	public function fieldEmptyOption($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && isset($this->_config->form->fields->{$fieldName}->emptyOption) ? $this->_config->form->fields->{$fieldName}->emptyOption : false;
	}
	public function fieldExcludedModes($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && isset($this->_config->form->fields->{$fieldName}->excludedModes) ? $this->_config->form->fields->{$fieldName}->excludedModes : array();
	}
	public function fieldId($fieldName) {
		$name = $this->virtualName();
		return $name ? "{$name}_{$fieldName}" : $fieldName;
	}
	public function fieldLabel($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->label : '';
	}
	public function fields() {
		$this->load();
		return array_keys(get_object_vars($this->_config->form->fields));
	}
	public function fieldType($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->type : false;
	}
	public function fieldValue($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) ? $this->_config->form->fields->{$fieldName}->value : '';
	}
	public function fieldValues($fieldName) {
		$this->load();
		return isset($this->_config->form->fields->{$fieldName}) && $this->_config->form->fields->{$fieldName}->type == GC_FORMS_FIELDTYPE_ENUM ? $this->_config->form->fields->{$fieldName}->values : array();
	}
	public function id() {
		return $this->virtualName();
	}
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
	public function isReadOnly($mode) {
		$out = $this->_config->form->readonly || $mode == GC_FORMS_BUILDMODE_VIEW || $mode == GC_FORMS_BUILDMODE_REMOVE;

		if(!$out && isset($this->_config->form->modes->{$mode}->readonly)) {
			$out = $this->_config->form->modes->{$mode}->readonly;
		}

		return $out;
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

		$out = $this->_config->form->method;

		if($mode && isset($this->_config->form->modes->{$mode}) && isset($this->_config->form->modes->{$mode}->method)) {
			$out = $this->_config->form->modes->{$mode}->method;
		}

		return $out;
	}
	public function modes() {
		//
		// Loading all required settings.
		$this->load();

		return isset($this->_config->form->modes) ? array_keys(get_object_vars($this->_config->form->modes)) : array();
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
	public function type() {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Loading all required settings.
		$this->load();

		return isset($this->_config->form->type) ? $this->_config->form->type : $Defaults[GC_DEFAULTS_FORMS_TYPE];
	}
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
		if(!isset($this->_config->form)) {
			throw new FormsException("Wrong form specification, unable to find path '///form'.");
		}
		if(!isset($this->_config->form->fields) || !count(get_object_vars($this->_config->form->fields))) {
			throw new FormsException("Wrong form specification, unable to find path '///form/fields' or maybe empty.");
		}
		//
		// Checking and expanding default form values.
		$formFields = array(
			'type' => $Defaults[GC_DEFAULTS_FORMS_TYPE],
			'name' => '',
			'action' => '#',
			'method' => 'get',
			'attrs' => false,
			'modes' => false,
			'buttons' => false,
			'readonly' => false
		);
		$this->_config->form = \TooBasic\objectCopyAndEnforce(array_keys($formFields), new \stdClass(), $this->_config->form, $formFields);
		//
		// Required form objects.
		foreach(array('attrs', 'modes', 'buttons') as $name) {
			if(!$this->_config->form->{$name}) {
				$this->_config->form->{$name} = new \stdClass();
			}
		}
		//
		// Checking and expanding default field values.
		$fieldFields = array(
			'type' => 'input',
			'attrs' => false,
			'value' => '',
			'label' => false,
			'values' => array(),
			'excludedModes' => array()
		);
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
			foreach(array('attrs') as $subName) {
				if(!$config->{$subName}) {
					$config->{$subName} = new \stdClass();
				}
			}
			//
			// Special checks for 'enum' fields.
			if($config->type == 'enum' && isset($config->emptyOption)) {
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
		$buttonFields = array(
			'type' => 'button',
			'attrs' => false,
			'label' => false
		);
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
			foreach(array('attrs') as $subName) {
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
				// Loading configuration.
				$this->_config = json_decode(file_get_contents($this->path()));
				//
				// Checking configuration.
				if($this->_config) {
					$this->_status = true;
					$this->checkConfig();
				}
			} elseif(!$this->path()) {
				throw new FormsException("Unknown form '{$this->name()}'.");
			} else {
				throw new FormsException("Unable to read form path '{$this->path()}'.");
			}
		}
	}
	/**
	 * This method provides access to a MagicProp instance shortcut.
	 *
	 * @return \TooBasic\MagicProp Returns the shortcut.
	 */
	protected function magic() {
		if($this->_magic === false) {
			$this->_magic = MagicProp::Instance();
		}

		return $this->_magic;
	}
}
