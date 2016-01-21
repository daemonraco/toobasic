<?php

namespace TooBasic\Forms;

//
// Class aliases.
use TooBasic\MagicProp;
use TooBasic\Names;
use TooBasic\Paths;

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
	}
	//
	// Public methods.
	public function buildFor($item, $mode = false, $flags = array()) {
		//
		// Default values.
		$out = '';
		//
		// Global dependencies.
		global $Defaults;
		//
		// Loading all required settings.
		$this->load();

		$builder = false;
		if(isset($Defaults[GC_DEFAULTS_FORMS_TYPES][$this->_config->form->type])) {
			$className = $Defaults[GC_DEFAULTS_FORMS_TYPES][$this->_config->form->type];
			$builder = new $className($this->config());
		} else {
			throw new FormsException("Unknown form type '{$this->_config->form->type}' at path '///form/type'.");
		}
		//
		// Fixing mode.
		if(!$mode) {
			$mode = GC_FORMS_BUILDMODE_EDIT;
		} elseif(!in_array($mode, array(GC_FORMS_BUILDMODE_CREATE, GC_FORMS_BUILDMODE_EDIT, GC_FORMS_BUILDMODE_REMOVE, GC_FORMS_BUILDMODE_VIEW))) {
			throw new FormsException("Unrecognized form mode '{$mode}'.");
		}
		//
		// Building form.
		$out = $builder->buildFor($item, $mode, $flags);

		return $out;
	}
	/**
	 * This method provides access to the loaded configuration.
	 *
	 * @return \stdClass Returns a configuration object.
	 */
	public function config() {
		//
		// Loading all required settings.
		$this->load();

		return $this->_config;
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
		//
		// Loading all required settings.
		$this->load();

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
	//
	// Protected methods.
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
		// Expanding default values.
		$formFields = array(
			'type' => $Defaults[GC_DEFAULTS_FORMS_TYPE],
			'name' => '',
			'action' => '#',
			'method' => 'get',
			'attrs' => false,
			'modes' => false,
			'buttons' => false
		);
		$this->_config->form = \TooBasic\objectCopyAndEnforce(array_keys($formFields), new \stdClass(), $this->_config->form, $formFields);
		//
		// Required objects.
		foreach(array('attrs', 'modes', 'buttons') as $name) {
			if(!$this->_config->form->{$name}) {
				$this->_config->form->{$name} = new \stdClass();
			}
		}
		//
		// Checking fields configuration.
		$fieldFields = array(
			'type' => 'input',
			'attrs' => false,
			'value' => '',
			'label' => false,
			'values' => array(),
			'excludedModes' => array()
		);
		foreach($this->_config->form->fields as $name => $config) {
			if(!is_object($config)) {
				$aux = new \stdClass();
				$aux->type = $config;
				$config = $aux;
			}

			$config = \TooBasic\objectCopyAndEnforce(array_keys($fieldFields), new \stdClass(), $config, $fieldFields);
			//
			// Field label.
			$fieldFields['label'] = !$fieldFields['label'] ? "label_formcontrol_{$name}" : $fieldFields['label'];
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
		if(isset($this->magic()->params->debugformconfig)) {
			\TooBasic\debugThing($this->_config);
		}
	}
	protected function checkConfigForButtons(&$buttons) {
		//
		// Checking buttons configuration.
		$buttonFields = array(
			'type' => 'button',
			'attrs' => false
		);
		foreach($buttons as $name => $config) {
			if(!is_object($config)) {
				$aux = new \stdClass();
				$aux->type = $config;
				$config = $aux;
			}
			$buttonFields['label'] = "btn_{$name}";

			$config = \TooBasic\objectCopyAndEnforce(array_keys($buttonFields), new \stdClass(), $config, $buttonFields);
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
			// Global dependencies.
			global $Paths;
			//
			// Guessing names.
			$fileName = Names::SnakeFilename($this->name());
			$this->_path = Paths::Instance()->customPaths($Paths[GC_PATHS_FORMS], $fileName, Paths::ExtensionJSON);
			//
			// Checking path existence.
			if($this->_path && is_readable($this->_path)) {
				$this->_config = json_decode(file_get_contents($this->_path));
				if($this->_config) {
					$this->_status = true;
					$this->checkConfig();
				}
			} elseif(!$this->_path) {
				throw new FormsException("Unknown form '{$this->name()}'.");
			} else {
				throw new FormsException("Unable to read form path '{$this->_path}'.");
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
