<?php

/**
 * @file table.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Names;
use TooBasic\Sanitizer;
use TooBasic\Shell\Option;

/**
 * @class TableSystool
 */
class TableSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OptionBootstrap = 'Bootstrap';
	const OptionColumn = 'Column';
	const OptionConnection = 'Connection';
	const OptionGenAutocomplete = 'GenAutocomplete';
	const OptionNameField = 'NameField';
	const OptionNoFormBuilder = 'NoFormBuilder';
	const OptionPlural = 'Plural';
	const OptionRaw = 'Raw';
	const OptionSearchable = 'Searchable';
	const OptionSpecsVersion = 'SpecsVersion';
	const OptionSystem = 'System';
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
				$opt = $this->_options->option(self::OptionConnection);
				if($opt->activated()) {
					$this->_assignments['connection'] = $opt->value();
				}
				//
				// Generating a table prefix.
				$this->_assignments['tableFields'] = array();
				$opt = $this->_options->option(self::OptionColumn);
				if($opt->activated()) {
					foreach($opt->value() as $column) {
						$columnParts = explode(':', $column);
						$field = array();
						$field['name'] = $columnParts[0];

						if(!isset($columnParts[1])) {
							$columnParts = 'varchar';
						}
						$columnParts[1] = strtolower($columnParts[1]);
						$field[GC_AFIELD_TYPE] = array();
						switch($columnParts[1]) {
							case 'int':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'int';
								$field[GC_AFIELD_TYPE]['precision'] = 11;
								$field['default'] = 0;
								$field['inForm'] = true;
								break;
							case 'blob':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'blob';
								$field[GC_AFIELD_TYPE]['precision'] = false;
								$field['default'] = '';
								$field['inForm'] = false;
								break;
							case 'float':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'float';
								$field[GC_AFIELD_TYPE]['precision'] = 11;
								$field['default'] = .0;
								$field['inForm'] = true;
								break;
							case 'text':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'text';
								$field[GC_AFIELD_TYPE]['precision'] = false;
								$field['default'] = '';
								$field['inForm'] = true;
								break;
							case 'timestamp':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'timestamp';
								$field[GC_AFIELD_TYPE]['precision'] = false;
								$field['default'] = 0;
								$field['inForm'] = false;
								break;
							case 'enum':
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'enum';
								$field[GC_AFIELD_TYPE]['precision'] = false;
								$field[GC_AFIELD_TYPE]['values'] = $columnParts;
								array_shift($field[GC_AFIELD_TYPE]['values']);
								array_shift($field[GC_AFIELD_TYPE]['values']);
								if($field[GC_AFIELD_TYPE]['values']) {
									$field[GC_AFIELD_TYPE]['values'] = array_values($field[GC_AFIELD_TYPE]['values']);
								} else {
									$field[GC_AFIELD_TYPE]['values'][0] = 'Y';
									$field[GC_AFIELD_TYPE]['values'][1] = 'N';
								}
								$field['default'] = "'{$field[GC_AFIELD_TYPE]['values'][0]}'";
								$field['inForm'] = true;
								break;
							case 'varchar':
							default:
								$field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] = 'varchar';
								$field[GC_AFIELD_TYPE]['precision'] = 256;
								$field['default'] = '';
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
						if($field['name'] == $this->_names['name-field']) {
							//
							// Checking type.
							if($field[GC_AFIELD_TYPE][GC_AFIELD_TYPE] != 'varchar') {
								$this->setError(self::ErrorWrongParameters, "Column '{$this->_names['name-field']}' is not a varchar field.");
							}
							$found = true;
							break;
						}
					}
					if(!$found) {
						$this->setError(self::ErrorWrongParameters, "Column '{$this->_names['name-field']}' was not specified.");
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
				$opt = $this->_options->option(self::OptionModule);
				if($opt->activated()) {
					$path = Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONFIGS]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = array();
					}

					$this->_configLines[$path][] = "function activateSearchFor{$this->_assignments['searchCode']}() {";
					$this->_configLines[$path][] = "\tglobal \$Search; //DEPENDENCY activateSearchFor{$this->_assignments['searchCode']}()";
					$this->_configLines[$path][] = "\t\\TooBasic\\MagicProp::Instance()->representation->{$this->_names['plural-name']};";
					$this->_configLines[$path][] = "\t\$Search[GC_SEARCH_ENGINE_FACTORIES]['{$this->_assignments['searchCode']}'] = '{$this->_names['factory-name']}';";
					$this->_configLines[$path][] = "} //ENDOF activateSearchFor{$this->_assignments['searchCode']}()";

					$path = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = array();
					}

					$this->_configLines[$path][] = "activateSearchFor{$this->_assignments['searchCode']}();";
				} else {
					$path = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/config.php");
					$this->_requiredDirectories[] = dirname($path);

					if(!isset($this->_configLines[$path])) {
						$this->_configLines[$path] = array();
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
				//
				// Checking form mechanism.
				$opt = $this->_options->option(self::OptionNoFormBuilder);
				$this->_names['form-builder'] = !$opt->activated();
				//
				// Checking plural name.
				$opt = $this->_options->option(self::OptionPlural);
				if($opt->activated()) {
					$this->_names['plural-name'] = $opt->value();
				}
				//
				// Checking bootstrap option.
				$opt = $this->_options->option(self::OptionBootstrap);
				if($opt->activated()) {
					$this->_names['templates-prefix'] = 'bootstrap/';
					$this->_names['templates-style'] = 'bootstrap';
				}
				//
				// Checking type.
				$opt = $this->_options->option(self::OptionSystem);
				if($opt->activated()) {
					$this->_names[GC_AFIELD_TYPE] = strtolower($opt->value());
				}
				//
				// Checking search engine flags.
				$opt = $this->_options->option(self::OptionSearchable);
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
				$this->_names['view-action'] = "{$this->_names['singular-name']}";
				$this->_names['view-action-controller'] = Names::ControllerClass($this->_names['view-action']);
				$this->_names['edit-action'] = "{$this->_names['singular-name']}_edit";
				$this->_names['edit-action-controller'] = Names::ControllerClass($this->_names['edit-action']);
				$this->_names['add-action'] = "{$this->_names['singular-name']}_add";
				$this->_names['add-action-controller'] = Names::ControllerClass($this->_names['add-action']);
				$this->_names['delete-action'] = "{$this->_names['singular-name']}_delete";
				$this->_names['delete-action-controller'] = Names::ControllerClass($this->_names['delete-action']);
				//
				// Name field and service.
				$opt = $this->_options->option(self::OptionNameField);
				if(!$this->isRaw() && $opt->activated()) {
					$this->_names['name-field'] = $opt->value();
					$this->_names['predictive-service'] = "{$this->_names['plural-name']}_predictive";
					$this->_names['predictive-service-controller'] = Names::ServiceClass($this->_names['predictive-service']);
				}
				//
				// Files
				$this->_files[] = array(
					GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_DBSPECS]}/{$this->_names['plural-name']}.json"),
					GC_AFIELD_GENERATOR => 'genSpecsFile',
					GC_AFIELD_DESCRIPTION => 'specifications file'
				);
				if(!$this->isRaw()) {
					if($this->_names['form-builder']) {
						$this->_files[] = array(
							GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_FORMS]}/table_{$this->_names['plural-name']}.json"),
							GC_AFIELD_GENERATOR => 'genFormBuilderFile',
							GC_AFIELD_DESCRIPTION => 'form builder specifications file'
						);
					}
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['representation-name']}.php"),
						GC_AFIELD_TEMPLATE => 'representation.html',
						GC_AFIELD_DESCRIPTION => 'representations file'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['factory-name']}.php"),
						GC_AFIELD_TEMPLATE => 'factory.html',
						GC_AFIELD_DESCRIPTION => 'representations factory file'
					);
					if(isset($this->_names['name-field'])) {
						$this->_files[] = array(
							GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_SERVICES]}/{$this->_names['predictive-service']}.php"),
							GC_AFIELD_TEMPLATE => 'predictive.html',
							GC_AFIELD_DESCRIPTION => 'predictive search service file'
						);
						$opt = $this->_options->option(self::OptionGenAutocomplete);
						if($opt->activated()) {
							$this->_files[] = array(
								GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_JS]}/{$this->_names['singular-name']}_predictive_{$this->_names['name-field']}.js"),
								GC_AFIELD_TEMPLATE => 'autocomplete.html',
								GC_AFIELD_DESCRIPTION => 'predictive search service file'
							);
						}
					}
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['list-action']}.php"),
						GC_AFIELD_TEMPLATE => 'list_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to list table items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['list-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}list.html",
						GC_AFIELD_DESCRIPTION => 'view file to list table items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['view-action']}.php"),
						GC_AFIELD_TEMPLATE => 'view_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to view items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['view-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}view.html",
						GC_AFIELD_DESCRIPTION => 'view file to view items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['edit-action']}.php"),
						GC_AFIELD_TEMPLATE => 'edit_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to edit items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['edit-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}edit.html",
						GC_AFIELD_DESCRIPTION => 'view file to edit items'
					);

					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['add-action']}.php"),
						GC_AFIELD_TEMPLATE => 'add_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to add items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['add-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}add.html",
						GC_AFIELD_DESCRIPTION => 'view file to add items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['delete-action']}.php"),
						GC_AFIELD_TEMPLATE => 'delete_controller.html',
						GC_AFIELD_DESCRIPTION => 'controller file to delete items'
					);
					$this->_files[] = array(
						GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['delete-action']}.html"),
						GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}delete.html",
						GC_AFIELD_DESCRIPTION => 'view file to delete items'
					);
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
					// List table items route.
					$route = new \stdClass();
					$route->route = $this->_names['list-action'];
					$route->action = $this->_names['list-action'];
					$this->_routes[] = $route;
					//
					// View table item route.
					$route = new \stdClass();
					$route->route = "{$this->_names['view-action']}/:id:";
					$route->action = $this->_names['view-action'];
					$this->_routes[] = $route;
					//
					// Edit table item route.
					$route = new \stdClass();
					$route->route = "{$this->_names['edit-action']}/:id:";
					$route->action = $this->_names['edit-action'];
					$this->_routes[] = $route;
					//
					// Add table item route.
					$route = new \stdClass();
					$route->route = "{$this->_names['add-action']}";
					$route->action = $this->_names['add-action'];
					$this->_routes[] = $route;
					//
					// Delete table item route.
					$route = new \stdClass();
					$route->route = "{$this->_names['delete-action']}/:id:";
					$route->action = $this->_names['delete-action'];
					$this->_routes[] = $route;
				}
			}
		}
	}
	protected function genFormBuilderFile($path, $template, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Assignments.
		$this->genAssignments();
		//
		// Main specs structure.
		$specs = new \stdClass();
		$specs->form = new \stdClass();
		//
		// Basic values.
		if($this->params->opt->{self::OptionBootstrap}) {
			$specs->form->type = GC_FORMS_BUILDTYPE_BOOTSTRAP;
		} else {
			$specs->form->type = GC_FORMS_BUILDTYPE_BASIC;
		}
		$specs->form->name = "{$this->_names['singular-name']}_form";
		$specs->form->method = 'post';
		//
		// Attributes.
		$specs->form->attrs = new \stdClass();
		$specs->form->attrs->role = 'form';
		$specs->form->attrs->{'ng-non-bindable'} = true;
		//
		// Fields.
		$specs->form->fields = new \stdClass();
		//
		// Adding an ID field.
		$field = new \stdClass();
		$field->type = 'hidden';
		$field->excludedModes = array(GC_FORMS_BUILDMODE_CREATE);
		$specs->form->fields->id = $field;
		//
		// Adding specified columns.
		foreach($this->_assignments['tableFields'] as $column) {
			$field = new \stdClass();
			$field->label = "table_column_{$column['name']}";
			$field->attrs = new \stdClass();
			$field->attrs->class = 'input-sm';

			switch($column[GC_AFIELD_TYPE][GC_AFIELD_TYPE]) {
				case 'enum':
					$field->type = 'enum';
					if(isset($column[GC_AFIELD_TYPE]['values'])) {
						$field->values = $column[GC_AFIELD_TYPE]['values'];
					}
					$field->value = '';
					$field->emptyOption = new \stdClass();
					$field->emptyOption->label = 'select_option_NOOPTION';
					$field->emptyOption->value = '';
					break;
				case 'text':
					$field->type = 'text';
					$field->value = $column['default'] ? $column['default'] : '';
					break;
				case 'int':
				case 'varchar':
				default:
					$field->type = 'input';
					$field->value = $column['default'] ? $column['default'] : '';
			}

			$specs->form->fields->{$column['name']} = $field;
		}
		//
		// Modes @{
		$specs->form->modes = new \stdClass();
		$specs->form->modes->create = new \stdClass();
		$specs->form->modes->edit = new \stdClass();
		$specs->form->modes->remove = new \stdClass();
		$specs->form->modes->remove->attrs = new \stdClass();
		$specs->form->modes->remove->attrs->onsubmit = "return confirm('Are you sure you want to remove this {$this->_names['singular-name']}?')";
		//
		// Create mode buttons.
		$specs->form->modes->create->buttons = new \stdClass();
		$specs->form->modes->create->buttons->add = new \stdClass();
		$specs->form->modes->create->buttons->add->type = 'submit';
		$specs->form->modes->create->buttons->add->attrs = new \stdClass();
		$specs->form->modes->create->buttons->add->attrs->class = 'btn-sm btn-success';
		$specs->form->modes->create->buttons->clear_fields = new \stdClass();
		$specs->form->modes->create->buttons->clear_fields->type = 'reset';
		$specs->form->modes->create->buttons->clear_fields->attrs = new \stdClass();
		$specs->form->modes->create->buttons->clear_fields->attrs->class = 'btn-sm btn-default';
		//
		// Edit mode buttons.
		$specs->form->modes->edit->buttons = new \stdClass();
		$specs->form->modes->edit->buttons->save = new \stdClass();
		$specs->form->modes->edit->buttons->save->type = 'submit';
		$specs->form->modes->edit->buttons->save->attrs = new \stdClass();
		$specs->form->modes->edit->buttons->save->attrs->class = 'btn-sm btn-success';
		$specs->form->modes->edit->buttons->restore_fields = new \stdClass();
		$specs->form->modes->edit->buttons->restore_fields->type = 'reset';
		$specs->form->modes->edit->buttons->restore_fields->attrs = new \stdClass();
		$specs->form->modes->edit->buttons->restore_fields->attrs->class = 'btn-sm btn-default';
		//
		// Remove mode buttons.
		$specs->form->modes->remove->buttons = new \stdClass();
		$specs->form->modes->remove->buttons->delete = new \stdClass();
		$specs->form->modes->remove->buttons->delete->label = "btn_delete_{$this->_names['singular-name']}";
		$specs->form->modes->remove->buttons->delete->type = 'submit';
		$specs->form->modes->remove->buttons->delete->attrs = new \stdClass();
		$specs->form->modes->remove->buttons->delete->attrs->class = 'btn-sm btn-danger';
		// @}
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
	protected function genSpecsFile($path, $template, &$error) {
		$out = false;

		$opt = $this->_options->option(self::OptionSpecsVersion);
		if(!$opt->activated() || ($opt->value() + 0) == 2) {
			$out = $this->genSpecsFileV2($path, $error);
		} elseif(($opt->value() + 0) == 1) {
			$out = $this->genSpecsFileV1($path, $error);
		} else {
			$this->setError(self::ErrorWrongParameters, "Unknown specifications file version '{$opt->value()}'");
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
		$specs = new \stdClass();
		$specs->tables = array();
		$specs->indexes = array();
		//
		// Table specs.
		$table = new \stdClass();
		$table->version = 1;
		$table->name = $this->_assignments['pluralName'];
		$table->prefix = $this->_assignments['tablePrefix'];
		if($this->_names[GC_AFIELD_TYPE] == 'mysql') {
			$table->engine = 'myisam';
		}
		if(isset($this->_assignments['connection'])) {
			$table->connection = $this->_assignments['connection'];
		}
		$table->fields = array();
		//
		// Adding an id column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->name = 'id';
			$field->type = new \stdClass();
			$field->type->type = 'int';
			$field->type->precision = 11;
			$field->null = false;
			$field->autoincrement = true;
			$table->fields[] = $field;
		}
		//
		// Adding specified columns.
		foreach($this->_assignments['tableFields'] as $column) {
			$field = new \stdClass();
			$field->name = $column['name'];
			$field->type = new \stdClass();
			$field->type->type = $column[GC_AFIELD_TYPE][GC_AFIELD_TYPE];
			if(isset($column[GC_AFIELD_TYPE]['values'])) {
				$field->type->values = $column[GC_AFIELD_TYPE]['values'];
			} else {
				$field->type->precision = $column[GC_AFIELD_TYPE]['precision'];
			}
			$field->default = $column['default'];
			$field->null = $column['null'];

			$table->fields[] = $field;
		}
		//
		// Adding a creation date column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->name = 'create_date';
			$field->type = new \stdClass();
			$field->type->type = 'timestamp';
			$field->type->precision = false;
			$field->null = false;
			$field->default = 'CURRENT_TIMESTAMP';
			$table->fields[] = $field;
		}
		//
		// Adding an indexation status column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->name = 'indexed';
			$field->type = new \stdClass();
			$field->type->type = 'varchar';
			$field->type->precision = 1;
			$field->null = false;
			$field->default = 'N';
			$table->fields[] = $field;
		}
		//
		// Adding table.
		$specs->tables[] = $table;
		//
		// Adding a primary key for column 'id'.
		if($this->_names[GC_AFIELD_TYPE] != 'mysql') {
			$index = new \stdClass();
			$index->name = "{$this->_assignments['tablePrefix']}id";
			$index->table = $this->_assignments['pluralName'];
			if(isset($this->_assignments['connection'])) {
				$index->connection = $this->_assignments['connection'];
			}
			$index->type = 'primary';
			$index->fields = array('id');
			$specs->indexes[] = $index;
		}
		//
		// Adding unique index for name field.
		if(isset($this->_assignments['nameField'])) {
			$index = new \stdClass();
			$index->name = "{$this->_assignments['tablePrefix']}{$this->_assignments['nameField']}";
			$index->table = $this->_assignments['pluralName'];
			if(isset($this->_assignments['connection'])) {
				$index->connection = $this->_assignments['connection'];
			}
			$index->type = 'key';
			$index->fields = array($this->_assignments['nameField']);
			$specs->indexes[] = $index;
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
		$specs = new \stdClass();
		$specs->tables = array();
		//
		// Table specs.
		$table = new \stdClass();
		$table->version = 2;
		$table->name = $this->_assignments['pluralName'];
		$table->prefix = $this->_assignments['tablePrefix'];
		if($this->_names[GC_AFIELD_TYPE] == 'mysql') {
			$table->engine = 'myisam';
		}
		if(isset($this->_assignments['connection'])) {
			$table->connection = $this->_assignments['connection'];
		}
		$table->fields = array();
		//
		// Adding an id column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->type = 'int';
			$field->autoincrement = true;
			$table->fields['id'] = $field;
		}
		//
		// Adding specified columns.
		foreach($this->_assignments['tableFields'] as $column) {
			$field = new \stdClass();
			$field->type = $column[GC_AFIELD_TYPE][GC_AFIELD_TYPE];
			if(isset($column[GC_AFIELD_TYPE]['values'])) {
				$field->type = "{$field->type}:".implode(':', $column[GC_AFIELD_TYPE]['values']);
			}
			if($column['default']) {
				$field->default = $column['default'];
			} else {
				$field = $field->type;
			}

			$table->fields[$column['name']] = $field;
		}
		//
		// Adding a creation date column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->type = 'timestamp';
			$field->null = false;
			$field->default = 'CURRENT_TIMESTAMP';
			$table->fields['create_date'] = $field;
		}
		//
		// Adding an indexation status column.
		if(!$this->isRaw()) {
			$field = new \stdClass();
			$field->type = 'varchar:1';
			$field->null = false;
			$field->default = 'N';
			$table->fields['indexed'] = $field;
		}
		//
		// Adding a primary key for column 'id'.
		if($this->_names[GC_AFIELD_TYPE] != 'mysql') {
			$table->primary = new \stdClass();
			$table->primary->id = array(
				"id"
			);
		}
		//
		// Adding unique index for name field.
		if(isset($this->_assignments['nameField'])) {
			$table->key = new \stdClass();
			$table->key->{$this->_assignments['nameField']} = array(
				$this->_assignments['nameField']
			);
		}
		//
		// Adding table.
		$specs->tables[] = $table;
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
			$auxTr = new \stdClass();
			$auxTr->key = "btn_delete_{$this->_names['singular-name']}";
			$auxTr->value = 'Delete this '.ucwords(str_replace('_', ' ', $this->_names['singular-name']));
			$this->_translations[] = $auxTr;

			foreach($this->_assignments['tableFields'] as $column) {
				$auxTr = new \stdClass();
				$auxTr->key = "table_column_{$column['name']}";
				$auxTr->value = ucwords(str_replace('_', ' ', $column['name']));
				$this->_translations[] = $auxTr;

				if(isset($column[GC_AFIELD_TYPE]['values'])) {
					foreach($column[GC_AFIELD_TYPE]['values'] as $option) {
						$auxTr = new \stdClass();
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
			$this->_raw = $this->_options->option(self::OptionRaw)->activated();
		}

		return $this->_raw;
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your tables.');

		parent::setOptions();

		$text = 'Allows you to create a new table and deploy it in your site. ';
		$text.= 'Name must be in singular, this tool will guess the plural version, but if it\'s wrong you can use the option \'--plural\'';
		$this->_options->option(self::OptionCreate)->setHelpText($text, 'name');

		$text = 'Allows you to eliminate a table from your site. ';
		$this->_options->option(self::OptionRemove)->setHelpText($text, 'name');

		$text = 'Adds a column to your table specification. ';
		$text.= "By default all columns are varchar(256) unless you specify something like this:\n";
		$text.= "\t- colname:blob Column named 'colname' of type BLOB.\n";
		$text.= "\t- colname:enum:va1:val2:val3 Column named 'colname' of type ENUM using 'val1', 'val2'  'val3' as possible values.\n";
		$text.= "\t- colname:float Column named 'colname' of type FLOAT.\n";
		$text.= "\t- colname:int Column named 'colname' of type NUMBER/INTEGER.\n";
		$text.= "\t- colname:text Column named 'colname' of type TEXT.\n";
		$text.= "\t- colname:timestamp Column named 'colname' of type TIMESTAMP.\n";
		$text.= "\t- colname:varchar Column named 'colname' of type VARCHAR(256) (this is the default).";
		$this->_options->addOption(Option::EasyFactory(self::OptionColumn, array('--column', '-c'), Option::TypeMultiValue, $text, 'name'));

		$text = "This parameters indicates which column shoud be consided as an unique name.";
		$this->_options->addOption(Option::EasyFactory(self::OptionNameField, array('--name-field', '-nf'), Option::TypeValue, $text, 'name'));

		$text = "If your table doesn't use the default connection, you may specify it with this parameter.";
		$this->_options->addOption(Option::EasyFactory(self::OptionConnection, array('--connection', '-C'), Option::TypeValue, $text, 'name'));

		$text = "If your plural names are getting all messed up, specify the value you want with this parameters.\n";
		$text.= "For example, the plural of 'person' isn't 'persons', it's 'people'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionPlural, array('--plural', '-P'), Option::TypeValue, $text, 'plural-name'));

		$text = "Sometimes there are specific matters related to the type of database you are using, so this parameters allows you to hint what your using, 'mysql', 'sqlite', etc.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSystem, array('--type'), Option::TypeValue, $text, 'db-type'));

		$text = "If you want to create a simple table without default columns and indexes, use this parameter.\n";
		$text = "Note: This won't create controllers and ohter related stuff.";
		$this->_options->addOption(Option::EasyFactory(self::OptionRaw, array('--raw', '-r'), Option::TypeNoValue, $text));

		$text = "Sets the specifications file version, possible values are: '1' and '2'.\n";
		$text.= "By default it assumes '2'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSpecsVersion, array('--specs-version', '-sv'), Option::TypeValue, $text));

		$text = 'This parameter activates the generation of JS scripts for autocompletion. ';
		$text.= "It depends on parameter '--name-field'.";
		$this->_options->addOption(Option::EasyFactory(self::OptionGenAutocomplete, array('--autocomplete', '-ac'), Option::TypeNoValue, $text));

		$text = 'All generated view will have a bootstrap structure.';
		$this->_options->addOption(Option::EasyFactory(self::OptionBootstrap, array('--bootstrap', '-bs'), Option::TypeNoValue, $text));

		$text = "When this option is given, generated representations and factories incorporate TooBasic's search engine logic.\n";
		$text.= "Given value is used as item type for searchable items indexation.";
		$this->_options->addOption(Option::EasyFactory(self::OptionSearchable, array('--searchable', '-sr'), Option::TypeValue, $text, 'item-code'));

		$text = "This option disables the use of form builders to generate each form.";
		$this->_options->addOption(Option::EasyFactory(self::OptionNoFormBuilder, array('--no-form-builder', '-nofb'), Option::TypeNoValue, $text));
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
