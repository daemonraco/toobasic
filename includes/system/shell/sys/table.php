<?php

/**
 * @file table.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Forms\Form;
use TooBasic\Forms\FormsManager;
use TooBasic\Forms\FormWriter;
use TooBasic\Names;
use TooBasic\Sanitizer;
use TooBasic\Shell\Option;

/**
 * @class TableSystool
 */
class TableSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OPTION_BOOTSTRAP = 'Bootstrap';
	const OPTION_COLUMN = 'Column';
	const OPTION_CONNECTION = 'Connection';
	const OPTION_GEN_AUTOCOMPLETE = 'GenAutocomplete';
	const OPTION_NAME_FIELD = 'NameField';
	const OPTION_NO_FORM_BUILDER = 'NoFormBuilder';
	const OPTION_PLURAL = 'Plural';
	const OPTION_RAW = 'Raw';
	const OPTION_SEARCHABLE = 'Searchable';
	const OPTION_SPECS_VERSION = 'SpecsVersion';
	const OPTION_SYSTEM = 'System';
	const PRECISION_NAME_FIELD = 64;
	const PRECISION_VARCHAR = 256;
	//
	// Protected properties.
	protected $_raw = null;
	protected $_scaffoldName = 'table';
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments() {
		$this->genNames();
		if($this->_assignments === false) {
			//
			// Parent standards.
			parent::genAssignments();
			//
			// Ingnoring process when there are previous errors.
			if(!$this->hasErrors()) {
				//
				// Assignments.
				$this->_assignments['singularName'] = $this->_names['singular-name'];
				$this->_assignments['pluralName'] = $this->_names['plural-name'];
				$this->_assignments['templatesStyle'] = $this->_names['templates-style'];
				$this->_assignments['isRaw'] = $this->isRaw();
				$this->_assignments['formBuilder'] = $this->_names['form-builder'];
				//
				// Searchable items flags.
				if(isset($this->_names['search-code'])) {
					$this->_assignments['isSearchable'] = true;
					$this->_assignments['searchCode'] = $this->_names['search-code'];
				} else {
					$this->_assignments['isSearchable'] = false;
				}
				//
				// Generating a table prefix.
				$this->_assignments['tablePrefix'] = substr(str_replace('_', '', $this->_names['plural-name']), 0, 3).'_';
				//
				// Connection settings.
				$opt = $this->_options->option(self::OPTION_CONNECTION);
				if($opt->activated()) {
					$this->_assignments['connection'] = $opt->value();
				}
				//
				// Generating a table prefix.
				$this->_assignments['tableFields'] = [];
				$opt = $this->_options->option(self::OPTION_COLUMN);
				if($opt->activated()) {
					foreach($opt->value() as $column) {
						$columnParts = explode(':', $column);
						$field = [];
						$field[GC_AFIELD_NAME] = $columnParts[0];

						if(!isset($columnParts[1])) {
							$columnParts = 'varchar';
						}
						$columnParts[1] = strtolower($columnParts[1]);
						$field[GC_AFIELD_TYPE] = [];
						switch($columnParts[1]) {
							case 'int':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'int';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = 11;
								$field[GC_AFIELD_DEFAULT] = 0;
								$field['inForm'] = true;
								break;
							case 'blob':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'blob';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = false;
								$field[GC_AFIELD_DEFAULT] = '';
								$field['inForm'] = false;
								break;
							case 'float':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'float';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = 11;
								$field[GC_AFIELD_DEFAULT] = .0;
								$field['inForm'] = true;
								break;
							case 'text':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'text';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = false;
								$field[GC_AFIELD_DEFAULT] = '';
								$field['inForm'] = true;
								break;
							case 'timestamp':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'timestamp';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = false;
								$field[GC_AFIELD_DEFAULT] = 0;
								$field['inForm'] = false;
								break;
							case 'enum':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'enum';
								$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = false;
								$field[GC_AFIELD_TYPE][GC_AFIELD_VALUES] = $columnParts;
								array_shift($field[GC_AFIELD_TYPE][GC_AFIELD_VALUES]);
								array_shift($field[GC_AFIELD_TYPE][GC_AFIELD_VALUES]);
								if($field[GC_AFIELD_TYPE][GC_AFIELD_VALUES]) {
									$field[GC_AFIELD_TYPE][GC_AFIELD_VALUES] = array_values($field[GC_AFIELD_TYPE][GC_AFIELD_VALUES]);
								} else {
									$field[GC_AFIELD_TYPE][GC_AFIELD_VALUES][0] = 'Y';
									$field[GC_AFIELD_TYPE][GC_AFIELD_VALUES][1] = 'N';
								}
								$field[GC_AFIELD_DEFAULT] = "'{$field[GC_AFIELD_TYPE][GC_AFIELD_VALUES][0]}'";
								$field['inForm'] = true;
								break;
							case 'varchar':
							default:
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'varchar';
								if(isset($this->_names['name-field']) && $this->_names['name-field'] == $field[GC_AFIELD_NAME]) {
									$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = self::PRECISION_NAME_FIELD;
								} else {
									$field[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] = self::PRECISION_VARCHAR;
								}
								$field[GC_AFIELD_DEFAULT] = '';
								$field['inForm'] = true;
						}
						$field['null'] = false;
						$this->_assignments['tableFields'][] = $field;
					}
				}
				//
				// Representations.
				$this->_assignments['representationName'] = $this->_names['representation-name'];
				$this->_assignments['factoryName'] = $this->_names['factory-name'];
				//
				// List table items controller.
				$this->_assignments['listAction'] = $this->_names['list-action'];
				$this->_assignments['listActionController'] = $this->_names['list-action-controller'];
				//
				// List table items controller.
				$this->_assignments['viewAction'] = $this->_names['view-action'];
				$this->_assignments['viewActionController'] = $this->_names['view-action-controller'];
				//
				// List table items controller.
				$this->_assignments['editAction'] = $this->_names['edit-action'];
				$this->_assignments['editActionController'] = $this->_names['edit-action-controller'];
				//
				// List table items controller.
				$this->_assignments['addAction'] = $this->_names['add-action'];
				$this->_assignments['addActionController'] = $this->_names['add-action-controller'];
				//
				// List table items controller.
				$this->_assignments['deleteAction'] = $this->_names['delete-action'];
				$this->_assignments['deleteActionController'] = $this->_names['delete-action-controller'];
				//
				// Predictive service items.
				if(isset($this->_names['name-field'])) {
					//
					// Checking name field.
					$found = false;
					foreach($this->_assignments['tableFields'] as $field) {
						if($field[GC_AFIELD_NAME] == $this->_names['name-field']) {
							//
							// Checking type.
							if($field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] != 'varchar') {
								$this->setError(self::ERROR_WRONG_PARAMETERS, "Column '{$this->_names['name-field']}' is not a varchar field.");
							}
							$found = true;
							break;
						}
					}
					if(!$found) {
						$this->setError(self::ERROR_WRONG_PARAMETERS, "Column '{$this->_names['name-field']}' was not specified.");
					}
					//
					// Assigning fields.
					$this->_assignments['nameField'] = $this->_names['name-field'];
					$this->_assignments['predictiveService'] = $this->_names['predictive-service'];
					$this->_assignments['predictiveServiceController'] = $this->_names['predictive-service-controller'];
				}
			}
		}
	}
	protected function genConfigLines() {
		$this->genNames();
		if($this->_configLines === false) {
			//
			// Parent standards.
			parent::genConfigLines();
			//
			// Global depdendencies.
			global $Directories;
			global $Paths;
			//
			// Adding searchable configuration.
			if(isset($this->_names['search-code'])) {
				//
				// Checking module and parent directory.
				$opt = $this->_options->option(self::OPTION_MODULE);
				if($opt->activated()) {
					$path = Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONFIGS]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = [];
					}

					$this->_configLines[$path][] = "function activateSearchFor{$this->_assignments['searchCode']}() {";
					$this->_configLines[$path][] = "\tglobal \$Search; //DEPENDENCY activateSearchFor{$this->_assignments['searchCode']}()";
					$this->_configLines[$path][] = "\t\\TooBasic\\MagicProp::Instance()->representation->{$this->_names['plural-name']};";
					$this->_configLines[$path][] = "\t\$Search[GC_SEARCH_ENGINE_FACTORIES]['{$this->_assignments['searchCode']}'] = '{$this->_names['factory-name']}';";
					$this->_configLines[$path][] = "} //ENDOF activateSearchFor{$this->_assignments['searchCode']}()";

					$path = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = [];
					}

					$this->_configLines[$path][] = "activateSearchFor{$this->_assignments['searchCode']}();";
				} else {
					$path = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = [];
					}

					$this->_configLines[$path][] = "\\TooBasic\\MagicProp::Instance()->representation->{$this->_names['plural-name']};";
					$this->_configLines[$path][] = "\$Search[GC_SEARCH_ENGINE_FACTORIES]['{$this->_assignments['searchCode']}'] = '{$this->_names['factory-name']}';";
				}
			}
		}
	}
	protected function genNames() {
		if($this->_names === false) {
			//
			// Parent standards.
			parent::genNames();
			//
			// Ingnoring process when there are previous errors.
			if(!$this->hasErrors()) {
				//
				// Global dependencies.
				global $Paths;

				$this->_names[GC_AFIELD_TYPE] = false;
				$this->_names['singular-name'] = $this->_names[GC_AFIELD_NAME];
				$this->_names['plural-name'] = "{$this->_names[GC_AFIELD_NAME]}s";
				$this->_names['templates-prefix'] = '';
				$this->_names['templates-style'] = 'default';
				//
				// Checking form mechanism.
				$opt = $this->_options->option(self::OPTION_NO_FORM_BUILDER);
				$this->_names['form-builder'] = !$opt->activated();
				//
				// Checking plural name.
				$opt = $this->_options->option(self::OPTION_PLURAL);
				if($opt->activated()) {
					$this->_names['plural-name'] = $opt->value();
				}
				//
				// Checking bootstrap option.
				$opt = $this->_options->option(self::OPTION_BOOTSTRAP);
				if($opt->activated()) {
					$this->_names['templates-prefix'] = 'bootstrap/';
					$this->_names['templates-style'] = 'bootstrap';
				}
				//
				// Checking type.
				$opt = $this->_options->option(self::OPTION_SYSTEM);
				if($opt->activated()) {
					$this->_names[GC_AFIELD_TYPE] = strtolower($opt->value());
				}
				//
				// Checking search engine flags.
				$opt = $this->_options->option(self::OPTION_SEARCHABLE);
				if($opt->activated()) {
					$this->_names['search-code'] = substr(strtoupper($opt->value()), 0, 10);
				}
				//
				// Representations.
				$this->_names['representation-name'] = Names::ItemRepresentationClass($this->_names['singular-name']);
				$this->_names['factory-name'] = Names::ItemsFactoryClass($this->_names['plural-name']);
				//
				// Actions.
				$this->_names['list-action'] = $this->_names['plural-name'];
				$this->_names['list-action-controller'] = Names::ControllerClass($this->_names['list-action']);
				$this->_names['view-action'] = $this->_names['singular-name'];
				$this->_names['view-action-controller'] = Names::ControllerClass($this->_names['view-action']);
				$this->_names['edit-action'] = "{$this->_names['singular-name']}_edit";
				$this->_names['edit-action-controller'] = Names::ControllerClass($this->_names['edit-action']);
				$this->_names['add-action'] = "{$this->_names['singular-name']}_add";
				$this->_names['add-action-controller'] = Names::ControllerClass($this->_names['add-action']);
				$this->_names['delete-action'] = "{$this->_names['singular-name']}_delete";
				$this->_names['delete-action-controller'] = Names::ControllerClass($this->_names['delete-action']);
				//
				// Name field and service.
				$opt = $this->_options->option(self::OPTION_NAME_FIELD);
				if(!$this->isRaw() && $opt->activated()) {
					$this->_names['name-field'] = $opt->value();
					$this->_names['predictive-service'] = "{$this->_names['plural-name']}_predictive";
					$this->_names['predictive-service-controller'] = Names::ServiceClass($this->_names['predictive-service']);
				}
				//
				// Files
				$this->_files[] = [
					GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_DBSPECS]}/{$this->_names['plural-name']}.json"),
					GC_AFIELD_GENERATOR => 'genSpecsFile',
					GC_AFIELD_DESCRIPTION => 'specifications file'
				];
				if(!$this->isRaw()) {
					if($this->_names['form-builder']) {
						$this->_files[] = [
							GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_FORMS]}/table_{$this->_names['plural-name']}.json"),
							GC_AFIELD_GENERATOR => 'genFormBuilderFile',
							GC_AFIELD_DESCRIPTION => 'form builder specifications file'
						];
					}
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['plural-name']}.json"),
						GC_AFIELD_GENERATOR => 'genCorePropsFile',
						GC_AFIELD_DESCRIPTION => 'core properties file'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['representation-name']}.php"),
						GC_AFIELD_TEMPLATE => 'representation.html',
						GC_AFIELD_DESCRIPTION => 'representations file'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['factory-name']}.php"),
						GC_AFIELD_TEMPLATE => 'factory.html',
						GC_AFIELD_DESCRIPTION => 'representations factory file'
					];
					if(isset($this->_names['name-field'])) {
						$this->_files[] = [
							GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_SERVICES]}/{$this->_names['predictive-service']}.php"),
							GC_AFIELD_TEMPLATE => 'predictive.html',
							GC_AFIELD_DESCRIPTION => 'predictive search service file'
						];
						$opt = $this->_options->option(self::OPTION_GEN_AUTOCOMPLETE);
						if($opt->activated()) {
							$this->_files[] = [
								GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_JS]}/{$this->_names['singular-name']}_predictive_{$this->_names['name-field']}.js"),
								GC_AFIELD_TEMPLATE => 'autocomplete.html',
								GC_AFIELD_DESCRIPTION => 'predictive search service file'
							];
						}
					}
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['list-action']}.php"),
						GC_AFIELD_TEMPLATE => 'list_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to list table items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['list-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}list.html",
						GC_AFIELD_DESCRIPTION => 'view file to list table items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['view-action']}.php"),
						GC_AFIELD_TEMPLATE => 'view_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to view items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['view-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}view.html",
						GC_AFIELD_DESCRIPTION => 'view file to view items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['edit-action']}.php"),
						GC_AFIELD_TEMPLATE => 'edit_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to edit items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['edit-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}edit.html",
						GC_AFIELD_DESCRIPTION => 'view file to edit items'
					];

					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['add-action']}.php"),
						GC_AFIELD_TEMPLATE => 'add_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to add items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['add-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}add.html",
						GC_AFIELD_DESCRIPTION => 'view file to add items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['delete-action']}.php"),
						GC_AFIELD_TEMPLATE => 'delete_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to delete items'
					];
					$this->_files[] = [
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['delete-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}delete.html",
						GC_AFIELD_DESCRIPTION => 'view file to delete items'
					];
				}
			}
		}
	}
	protected function genRoutes() {
		if($this->_routes === false) {
			parent::genRoutes();
			//
			// Ingnoring process when there are previous errors.
			if(!$this->hasErrors()) {
				if(!$this->isRaw()) {
					//
					// Setting table routes.
					$tableRoute = (object) [];
					$tableRoute->singularName = $this->_names['singular-name'];
					$tableRoute->pluralName = $this->_names['plural-name'];
					$tableRoute->searchable = isset($this->_names['search-code']) ? $this->_names['search-code'] : false;
					$tableRoute->predictive = isset($this->_names['predictive-service']) ? $this->_names['predictive-service'] : false;
					$this->_tableRoutes[] = $tableRoute;
				}
			}
		}
	}
	protected function genCorePropsFile($path, $template, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Assignments.
		$this->genAssignments();
		//
		// Main specs structure.
		$specs = [
			'table' => $this->_assignments['pluralName'],
			'representation_class' => $this->_assignments['singularName'],
			'columns_perfix' => $this->_assignments['tablePrefix'],
			'columns' => [
				'id' => 'id'
			],
			'read_only_columns' => [],
			'disable_create' => false,
			'column_filters' => (object) [],
			'extended_columns' => (object) [],
			'sub_lists' => (object) []
		];
		//
		// Name field.
		if(isset($this->_assignments['nameField'])) {
			$specs['columns']['name'] = $this->_assignments['nameField'];
			$specs['order_by'] = [
				$this->_assignments['nameField'] => 'asc'
			];
		}
		//
		// Searchable configurations.
		if($this->_assignments['isSearchable']) {
			$specs['column_filters'] = [
				'indexed' => GC_DATABASE_FIELD_FILTER_BOOLEAN
			];
		}
		//
		// Generating file content.
		$output = json_encode($specs, JSON_PRETTY_PRINT);

		$result = file_put_contents($path, $output);
		if($result === false) {
			$error = "Unable to write file '{$path}'";
			$out = false;
		}

		return $out;
	}
	protected function genFormBuilderFile($path, $template, &$error) {
		//
		// Assignments.
		$this->genAssignments();
		//
		// Helpers.
		$formsHelper = FormsManager::Instance();
		//
		// Default values.
		$out = true;
		$formPath = pathinfo($path);
		$formName = $formPath['filename'];
		//
		// Removing when forced.
		if($this->isForced()) {
			@unlink($path);
		}
		//
		// Main specs structure.
		$result = $formsHelper->createForm($formName, $this->_names[GC_AFIELD_MODULE_NAME]);
		if($result[GC_AFIELD_STATUS]) {
			$form = new Form($formName);
			$writer = new FormWriter($form);
			//
			// Basic values.
			$writer->setType($this->params->opt->{self::OPTION_BOOTSTRAP} ? GC_FORMS_BUILDTYPE_BOOTSTRAP : GC_FORMS_BUILDTYPE_TABLE);
			$writer->setName("{$this->_names['singular-name']}_form");
			$writer->setMethod('post');
			//
			// Attributes.
			$writer->setAttribute('role', 'form');
			$writer->setAttribute('ng-non-bindable', true);
			//
			// Fields @{
			//
			// Adding an ID field.
			$writer->addField('id', 'hidden');
			$writer->excludeFieldFrom('id', [GC_FORMS_BUILDMODE_CREATE]);
			//
			// Adding specified columns.
			foreach($this->_assignments['tableFields'] as $column) {
				switch($column[GC_AFIELD_TYPE][GC_AFIELD_TYPE]) {
					case 'enum':
						$type = 'enum';
						if(isset($column[GC_AFIELD_TYPE][GC_AFIELD_VALUES])) {
							$type.= ':'.implode(':', $column[GC_AFIELD_TYPE][GC_AFIELD_VALUES]);
						}
						$writer->addField($column[GC_AFIELD_NAME], $type);
						$writer->setFieldDefault($column[GC_AFIELD_NAME], '');
						$writer->setFieldEmptyOption($column[GC_AFIELD_NAME], 'select_option_NOOPTION', '');
						break;
					case 'text':
						$writer->addField($column[GC_AFIELD_NAME], 'text');
						$writer->setFieldDefault($column[GC_AFIELD_NAME], $column[GC_AFIELD_DEFAULT] ? $column[GC_AFIELD_DEFAULT] : '');
						break;
					case 'int':
					case 'varchar':
					default:
						$writer->addField($column[GC_AFIELD_NAME], 'input');
						$writer->setFieldDefault($column[GC_AFIELD_NAME], $column[GC_AFIELD_DEFAULT] ? $column[GC_AFIELD_DEFAULT] : '');
				}

				$writer->setFieldLabel($column[GC_AFIELD_NAME], "table_column_{$column[GC_AFIELD_NAME]}");
				$writer->setFieldAttribute($column[GC_AFIELD_NAME], 'class', 'input-sm');
			}
			//@}
			//
			// Modes @{
			$writer->setAttribute('onsubmit', "return confirm('Are you sure you want to remove this {$this->_names['singular-name']}?')", GC_FORMS_BUILDMODE_REMOVE);
			//
			// Create mode buttons.
			$writer->addButton('add', 'submit', GC_FORMS_BUILDMODE_CREATE);
			$writer->setButtonAttribute('add', 'class', 'btn-sm btn-success', GC_FORMS_BUILDMODE_CREATE);
			$writer->addButton('clear_fields', 'reset', GC_FORMS_BUILDMODE_CREATE);
			$writer->setButtonAttribute('clear_fields', 'class', 'btn-sm btn-default', GC_FORMS_BUILDMODE_CREATE);
			//
			// Edit mode buttons.
			$writer->addButton('save', 'submit', GC_FORMS_BUILDMODE_EDIT);
			$writer->setButtonAttribute('save', 'class', 'btn-sm btn-success', GC_FORMS_BUILDMODE_EDIT);
			$writer->addButton('restore_fields', 'reset', GC_FORMS_BUILDMODE_EDIT);
			$writer->setButtonAttribute('restore_fields', 'class', 'btn-sm btn-default', GC_FORMS_BUILDMODE_EDIT);
			//
			// Remove mode buttons.
			$writer->addButton('delete', 'submit', GC_FORMS_BUILDMODE_REMOVE);
			$writer->setButtonLabel('delete', "btn_delete_{$this->_names['singular-name']}", GC_FORMS_BUILDMODE_REMOVE);
			$writer->setButtonAttribute('delete', 'class', 'btn-sm btn-danger', GC_FORMS_BUILDMODE_REMOVE);
			// @}
			//
			// Saving changes.
			$writer->save();
		} else {
			$error = $result[GC_AFIELD_ERROR];
			$out = false;
		}

		return $out;
	}
	protected function genSpecsFile($path, $template, &$error) {
		$out = false;

		$opt = $this->_options->option(self::OPTION_SPECS_VERSION);
		if(!$opt->activated() || ($opt->value() + 0) == 2) {
			$out = $this->genSpecsFileV2($path, $error);
		} elseif(($opt->value() + 0) == 1) {
			$out = $this->genSpecsFileV1($path, $error);
		} else {
			$this->setError(self::ERROR_WRONG_PARAMETERS, "Unknown specifications file version '{$opt->value()}'");
		}


		return $out;
	}
	protected function genSpecsFileV1($path, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Assignments.
		$this->genAssignments();
		//
		// Main specs structure.
		$specs = [
			'tables' => [],
			'indexes' => []
		];
		//
		// Table specs.
		$table = [
			'version' => 1,
			'usesFormBuilder' => $this->_names['form-builder'],
			'name' => $this->_assignments['pluralName'],
			'prefix' => $this->_assignments['tablePrefix']
		];
		if($this->_names[GC_AFIELD_TYPE] == 'mysql') {
			$table['engine'] = 'myisam';
		}
		if(isset($this->_assignments['connection'])) {
			$table['connection'] = $this->_assignments['connection'];
		}
		$table['fields'] = [];
		//
		// Adding an id column.
		if(!$this->isRaw()) {
			$table['fields'][] = [
				'name' => 'id',
				'type' => [
					'type' => 'int',
					'precision' => 11
				],
				'null' => false,
				'autoincrement' => true
			];
		}
		//
		// Adding specified columns.
		foreach($this->_assignments['tableFields'] as $column) {
			$field = [
				'name' => $column[GC_AFIELD_NAME],
				'type' => [
					'type' => $column[GC_AFIELD_TYPE][GC_AFIELD_TYPE]
				],
				'default' => $column[GC_AFIELD_DEFAULT],
				'null' => $column[GC_AFIELD_NULL]
			];
			if(isset($column[GC_AFIELD_TYPE][GC_AFIELD_VALUES])) {
				$field['type']['values'] = $column[GC_AFIELD_TYPE][GC_AFIELD_VALUES];
			} else {
				$field['type']['precision'] = $column[GC_AFIELD_TYPE][GC_AFIELD_PRECISION];
			}

			$table['fields'][] = $field;
		}
		//
		// Adding a creation date column.
		if(!$this->isRaw()) {
			$table['fields'][] = [
				'name' => 'create_date',
				'type' => [
					'type' => 'timestamp',
					'precision' => false
				],
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP'
			];
		}
		//
		// Adding an indexation status column.
		if(!$this->isRaw()) {
			$table['fields'][] = [
				'name' => 'indexed',
				'type' => [
					'type' => 'varchar',
					'precision' => 1
				],
				'null' => false,
				'default' => 'N'
			];
		}
		//
		// Adding table.
		$specs['tables'][] = $table;
		//
		// Adding a primary key for column 'id'.
		if($this->_names[GC_AFIELD_TYPE] != 'mysql') {
			$index = [
				'name' => "{$this->_assignments['tablePrefix']}id",
				'table' => $this->_assignments['pluralName'],
				'type' => 'primary',
				'fields' => ['id']
			];
			if(isset($this->_assignments['connection'])) {
				$index['connection'] = $this->_assignments['connection'];
			}
			$specs['indexes'][] = $index;
		}
		//
		// Adding unique index for name field.
		if(isset($this->_assignments['nameField'])) {
			$index = [
				'name' => "{$this->_assignments['tablePrefix']}{$this->_assignments['nameField']}",
				'table' => $this->_assignments['pluralName'],
				'type' => 'key',
				'fields' => [$this->_assignments['nameField']]
			];
			if(isset($this->_assignments['connection'])) {
				$index['connection'] = $this->_assignments['connection'];
			}
			$specs['indexes'][] = $index;
		}
		//
		// Generating file content.
		$output = json_encode($specs, JSON_PRETTY_PRINT);

		$result = file_put_contents($path, $output);
		if($result === false) {
			$error = "Unable to write file '{$path}'";
			$out = false;
		}

		return $out;
	}
	protected function genSpecsFileV2($path, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Assignments.
		$this->genAssignments();
		//
		// Main specs structure.
		$specs = [
			'tables' => []
		];
		//
		// Table specs.
		$table = [
			'version' => 2,
			'usesFormBuilder' => $this->_names['form-builder'],
			'name' => $this->_assignments['pluralName'],
			'prefix' => $this->_assignments['tablePrefix']
		];
		if($this->_names[GC_AFIELD_TYPE] == 'mysql') {
			$table['engine'] = 'myisam';
		}
		if(isset($this->_assignments['connection'])) {
			$table['connection'] = $this->_assignments['connection'];
		}
		$table['fields'] = [];
		//
		// Adding an id column.
		if(!$this->isRaw()) {
			$table['fields']['id'] = [
				'type' => 'int',
				'autoincrement' => true
			];
		}
		//
		// Adding specified columns.
		foreach($this->_assignments['tableFields'] as $column) {
			$field = [
				'type' => $column[GC_AFIELD_TYPE][GC_AFIELD_TYPE]
			];
			if(isset($column[GC_AFIELD_TYPE][GC_AFIELD_VALUES])) {
				$field['type'] = "{$field['type']}:".implode(':', $column[GC_AFIELD_TYPE][GC_AFIELD_VALUES]);
			} elseif($field['type'] == 'varchar' && $column[GC_AFIELD_TYPE][GC_AFIELD_PRECISION] != self::PRECISION_VARCHAR) {
				$field['type'] = "{$field['type']}:{$column[GC_AFIELD_TYPE][GC_AFIELD_PRECISION]}";
			}
			if($column[GC_AFIELD_DEFAULT]) {
				$field['default'] = $column[GC_AFIELD_DEFAULT];
			} else {
				$field = $field['type'];
			}

			$table['fields'][$column[GC_AFIELD_NAME]] = $field;
		}
		//
		// Adding a creation date column.
		if(!$this->isRaw()) {
			$table['fields']['create_date'] = [
				'type' => 'timestamp',
				'null' => false,
				'default' => 'CURRENT_TIMESTAMP'
			];
		}
		//
		// Adding an indexation status column.
		if(!$this->isRaw()) {
			$table['fields']['indexed'] = [
				'type' => 'varchar:1',
				'null' => false,
				'default' => 'N'
			];
		}
		//
		// Adding a primary key for column 'id'.
		if($this->_names[GC_AFIELD_TYPE] != 'mysql') {
			$table['primary'] = [
				'id' => ['id']
			];
		}
		//
		// Adding unique index for name field.
		if(isset($this->_assignments['nameField'])) {
			$table['keys'] = [
				$this->_assignments['nameField'] => [$this->_assignments['nameField']]
			];
		}
		//
		// Adding table.
		$specs['tables'][] = $table;
		//
		// Generating file content.
		$output = json_encode($specs, JSON_PRETTY_PRINT);

		$result = file_put_contents($path, $output);
		if($result === false) {
			$error = "Unable to write file '{$path}'";
			$out = false;
		}

		return $out;
	}
	protected function genTranslations() {
		parent::genTranslations();
		//
		// Ingnoring process when there are previous errors.
		if(!$this->hasErrors() && !$this->isRaw()) {
			$this->genAssignments();
			//
			// Delete button label
			$auxTr = (object) [];
			$auxTr->key = "btn_delete_{$this->_names['singular-name']}";
			$auxTr->value = 'Delete this '.ucwords(str_replace('_', ' ', $this->_names['singular-name']));
			$this->_translations[] = $auxTr;

			foreach($this->_assignments['tableFields'] as $column) {
				$auxTr = (object) [];
				$auxTr->key = "table_column_{$column[GC_AFIELD_NAME]}";
				$auxTr->value = ucwords(str_replace('_', ' ', $column[GC_AFIELD_NAME]));
				$this->_translations[] = $auxTr;

				if(isset($column[GC_AFIELD_TYPE][GC_AFIELD_VALUES])) {
					foreach($column[GC_AFIELD_TYPE][GC_AFIELD_VALUES] as $option) {
						$auxTr = (object) [];
						$auxTr->key = "select_option_{$option}";
						$auxTr->value = ucwords(str_replace('_', ' ', $option));
						$this->_translations[] = $auxTr;
					}
				}
			}
		}
	}
	protected function isRaw() {
		if($this->_raw === null) {
			$this->_raw = $this->_options->option(self::OPTION_RAW)->activated();
		}

		return $this->_raw;
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your tables.');

		parent::setOptions();

		$text = 'Allows you to create a new table and deploy it in your site. ';
		$text.= 'Name must be in singular, this tool will guess the plural version, but if it\'s wrong you can use the option \'--plural\'';
		$this->_options->option(self::OPTION_CREATE)->setHelpText($text, 'name');

		$text = 'Allows you to eliminate a table from your site. ';
		$this->_options->option(self::OPTION_REMOVE)->setHelpText($text, 'name');

		$text = 'Adds a column to your table specification. ';
		$text.= "By default all columns are varchar(256) unless you specify something like this:\n";
		$text.= "\t- colname:blob Column named 'colname' of type BLOB.\n";
		$text.= "\t- colname:enum:va1:val2:val3 Column named 'colname' of type ENUM using 'val1', 'val2'  'val3' as possible values.\n";
		$text.= "\t- colname:float Column named 'colname' of type FLOAT.\n";
		$text.= "\t- colname:int Column named 'colname' of type NUMBER/INTEGER.\n";
		$text.= "\t- colname:text Column named 'colname' of type TEXT.\n";
		$text.= "\t- colname:timestamp Column named 'colname' of type TIMESTAMP.\n";
		$text.= "\t- colname:varchar Column named 'colname' of type VARCHAR(256) (this is the default).";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_COLUMN, ['--column', '-c'], Option::TYPE_MULTI_VALUE, $text, 'name'));

		$text = "This parameters indicates which column shoud be consided as an unique name.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_NAME_FIELD, ['--name-field', '-nf'], Option::TYPE_VALUE, $text, 'name'));

		$text = "If your table doesn't use the default connection, you may specify it with this parameter.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_CONNECTION, ['--connection', '-C'], Option::TYPE_VALUE, $text, 'name'));

		$text = "If your plural names are getting all messed up, specify the value you want with this parameters.\n";
		$text.= "For example, the plural of 'person' isn't 'persons', it's 'people'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_PLURAL, ['--plural', '-P'], Option::TYPE_VALUE, $text, 'plural-name'));

		$text = "Sometimes there are specific matters related to the type of database you are using, so this parameters allows you to hint what your using, 'mysql', 'sqlite', etc.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SYSTEM, ['--type'], Option::TYPE_VALUE, $text, 'db-type'));

		$text = "If you want to create a simple table without default columns and indexes, use this parameter.\n";
		$text = "Note: This won't create controllers and ohter related stuff.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_RAW, ['--raw', '-r'], Option::TYPE_NO_VALUE, $text));

		$text = "Sets the specifications file version, possible values are: '1' and '2'.\n";
		$text.= "By default it assumes '2'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SPECS_VERSION, ['--specs-version', '-sv'], Option::TYPE_VALUE, $text));

		$text = 'This parameter activates the generation of JS scripts for autocompletion. ';
		$text.= "It depends on parameter '--name-field'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_GEN_AUTOCOMPLETE, ['--autocomplete', '-ac'], Option::TYPE_NO_VALUE, $text));

		$text = 'All generated view will have a bootstrap structure.';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_BOOTSTRAP, ['--bootstrap', '-bs'], Option::TYPE_NO_VALUE, $text));

		$text = "When this option is given, generated representations and factories incorporate TooBasic's search engine logic.\n";
		$text.= "Given value is used as item type for searchable items indexation.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_SEARCHABLE, ['--searchable', '-sr'], Option::TYPE_VALUE, $text, 'item-code'));

		$text = "This option disables the use of form builders to generate each form.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_NO_FORM_BUILDER, ['--no-forms-builder', '-nofb'], Option::TYPE_NO_VALUE, $text));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating scaffold for '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing scaffold for '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskRemove($spacer);
	}
}
