<?php

use TooBasic\Shell\Color as TBS_Color;
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

class TableSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OptionBootstrap = 'Bootstrap';
	const OptionColumn = 'Column';
	const OptionConnection = 'Connection';
	const OptionPlural = 'Plural';
	const OptionRaw = 'Raw';
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
			// Assignments.
			$this->_assignments['singularName'] = $this->_names['singular-name'];
			$this->_assignments['pluralName'] = $this->_names['plural-name'];
			$this->_assignments['isRaw'] = $this->isRaw();
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
					$field['type'] = array();
					switch($columnParts[1]) {
						case 'int':
							$field['type']['type'] = 'int';
							$field['type']['precision'] = 11;
							$field['default'] = 0;
							$field['inForm'] = true;
							break;
						case 'blob':
							$field['type']['type'] = 'blob';
							$field['type']['precision'] = false;
							$field['default'] = '';
							$field['inForm'] = false;
							break;
						case 'float':
							$field['type']['type'] = 'float';
							$field['type']['precision'] = 11;
							$field['default'] = '';
							$field['inForm'] = true;
							break;
						case 'text':
							$field['type']['type'] = 'text';
							$field['type']['precision'] = false;
							$field['default'] = '';
							$field['inForm'] = true;
							break;
						case 'timestamp':
							$field['type']['type'] = 'timestamp';
							$field['type']['precision'] = false;
							$field['default'] = 0;
							$field['inForm'] = false;
							break;
						case 'enum':
							$field['type']['type'] = 'enum';
							$field['type']['precision'] = false;
							$field['type']['values'] = $columnParts;
							array_shift($field['type']['values']);
							array_shift($field['type']['values']);
							if($field['type']['values']) {
								$field['type']['values'] = array_values($field['type']['values']);
							} else {
								$field['type']['values'][0] = 'Y';
								$field['type']['values'][1] = 'N';
							}
							$field['default'] = "'{$field['type']['values'][0]}'";
							$field['inForm'] = true;
							break;
						case 'varchar':
						default:
							$field['type']['type'] = 'varchar';
							$field['type']['precision'] = 256;
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
		}
	}
	protected function genNames() {
		if($this->_names === false) {
			//
			// Parent standards.
			parent::genNames();
			//
			// Global dependencies.
			global $Paths;

			$this->_names['type'] = false;
			$this->_names['singular-name'] = $this->_names['name'];
			$this->_names['plural-name'] = "{$this->_names['name']}s";
			$this->_names['templates-prefix'] = '';
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
			}
			//
			// Checking type.
			$opt = $this->_options->option(self::OptionSystem);
			if($opt->activated()) {
				$this->_names['type'] = strtolower($opt->value());
			}
			//
			// Representations.
			$this->_names['representation-name'] = \TooBasic\classname($this->_names['singular-name']).GC_CLASS_SUFFIX_REPRESENTATION;
			$this->_names['factory-name'] = \TooBasic\classname($this->_names['plural-name']).GC_CLASS_SUFFIX_FACTORY;
			//
			// Actions.
			$this->_names['list-action'] = $this->_names['plural-name'];
			$this->_names['list-action-controller'] = \TooBasic\classname($this->_names['list-action']).GC_CLASS_SUFFIX_CONTROLLER;
			$this->_names['view-action'] = "{$this->_names['singular-name']}";
			$this->_names['view-action-controller'] = \TooBasic\classname($this->_names['view-action']).GC_CLASS_SUFFIX_CONTROLLER;
			$this->_names['edit-action'] = "{$this->_names['singular-name']}_edit";
			$this->_names['edit-action-controller'] = \TooBasic\classname($this->_names['edit-action']).GC_CLASS_SUFFIX_CONTROLLER;
			$this->_names['add-action'] = "{$this->_names['singular-name']}_add";
			$this->_names['add-action-controller'] = \TooBasic\classname($this->_names['add-action']).GC_CLASS_SUFFIX_CONTROLLER;
			$this->_names['delete-action'] = "{$this->_names['singular-name']}_delete";
			$this->_names['delete-action-controller'] = \TooBasic\classname($this->_names['delete-action']).GC_CLASS_SUFFIX_CONTROLLER;
			//
			// Files
			$this->_files[] = array(
				'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_DBSPECS]}/{$this->_names['plural-name']}.json"),
				'generator' => 'genSpecsFile',
				'description' => 'specifications file'
			);
			if(!$this->isRaw()) {
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['representation-name']}.php"),
					'template' => 'representation.html',
					'description' => 'representations file'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_REPRESENTATIONS]}/{$this->_names['factory-name']}.php"),
					'template' => 'factory.html',
					'description' => 'representations factory file'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['list-action']}.php"),
					'template' => 'list_controller.html',
					'description' => 'controller file to list table items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['list-action']}.html"),
					'template' => "{$this->_names['templates-prefix']}list.html",
					'description' => 'view file to list table items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['view-action']}.php"),
					'template' => 'view_controller.html',
					'description' => 'controller file to view items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['view-action']}.html"),
					'template' => "{$this->_names['templates-prefix']}view.html",
					'description' => 'view file to view items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['edit-action']}.php"),
					'template' => 'edit_controller.html',
					'description' => 'controller file to edit items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['edit-action']}.html"),
					'template' => "{$this->_names['templates-prefix']}edit.html",
					'description' => 'view file to edit items'
				);

				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['add-action']}.php"),
					'template' => 'add_controller.html',
					'description' => 'controller file to add items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['add-action']}.html"),
					'template' => "{$this->_names['templates-prefix']}add.html",
					'description' => 'view file to add items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['delete-action']}.php"),
					'template' => 'delete_controller.html',
					'description' => 'controller file to delete items'
				);
				$this->_files[] = array(
					'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['delete-action']}.html"),
					'template' => "{$this->_names['templates-prefix']}delete.html",
					'description' => 'view file to delete items'
				);
			}
		}
	}
	protected function genRoutes() {
		if($this->_routes === false) {
			parent::genRoutes();

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
	protected function genSpecsFile($path, $template, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Assignments.
		$this->genAssignments($this->_names);
		//
		// Main specs structure.
		$specs = new \stdClass();
		$specs->tables = array();
		$specs->indexes = array();
		//
		// Table specs.
		$table = new \stdClass();
		$table->name = $this->_assignments['pluralName'];
		$table->prefix = $this->_assignments['tablePrefix'];
		if($this->_names['type'] == 'mysql') {
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
			$field->type->type = $column['type']['type'];
			if(isset($column['type']['values'])) {
				$field->type->values = $column['type']['values'];
			} else {
				$field->type->precision = $column['type']['precision'];
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
		if($this->_names['type'] != 'mysql') {
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
		// Generating file content.
		$output = json_encode($specs, JSON_PRETTY_PRINT);

		$result = file_put_contents($path, $output);
		if($result === false) {
			$error = "Unable to write file '{$path}'";
			$out = false;
		}

		return $out;
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
		$text.= "\t- colname:varchar Column named 'colname' of type VARCHAR(256) (this is the default).\n";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionColumn, array('--column', '-c'), TBS_Option::TypeMultiValue, $text, 'name'));

		$text = "If your table doesn't use the default connection, you may specify it with this parameter.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionConnection, array('--connection', '-C'), TBS_Option::TypeValue, $text, 'name'));

		$text = "If your plural names are getting all messed up, specify the value you want with this parameters.\n";
		$text.= "For example, the plural of 'person' isn't 'persons', it's 'people'.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionPlural, array('--plural', '-P'), TBS_Option::TypeValue, $text, 'plural-name'));

		$text = "Sometimes there are specific matters related to the type of database you are using, so this parameters allows you to hint what your using, 'mysql', 'sqlite', etc.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionSystem, array('--type'), TBS_Option::TypeValue, $text, 'db-type'));

		$text = "If you want to create a simple table without default columns and indexes, use this parameter.\n";
		$text = "Note: This won't create controllers and ohter related stuff.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionRaw, array('--raw', '-r'), TBS_Option::TypeNoValue, $text));

		$text = "All generated view will have a bootstrap structure.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionBootstrap, array('--bootstrap', '-bs'), TBS_Option::TypeNoValue, $text));
	}
	protected function taskCreate($spacer = "") {
		$this->genNames();

		echo "{$spacer}Creating scaffold for '{$this->_names['name']}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskInfo($spacer = "") {
		
	}
	protected function taskRemove($spacer = "") {
		$this->genNames();

		echo "{$spacer}Removing scaffold for '{$this->_names['name']}':\n";

		return parent::taskRemove($spacer);
	}
}
