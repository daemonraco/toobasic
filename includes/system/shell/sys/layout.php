<?php

/**
 * @file layout.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Names;
use TooBasic\Sanitizer;
use TooBasic\Shell\Option;

/**
 * @class LayoutSystool
 */
class LayoutSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OPTION_CACHED = 'Cached';
	const OPTION_FLUID = 'Fluid';
	const OPTION_TYPE = 'Type';
	const TYPE_BASIC = 'basic';
	const TYPE_BOOTSTRAP = 'bootstrap';
	const TYPE_TABLE = 'table';
	//
	// Protected properties.
	protected $_scaffoldName = 'layout';
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments() {
		if($this->_assignments === false) {
			parent::genAssignments();
			//
			// Assignments.
			$this->_assignments['name'] = $this->_names[GC_AFIELD_NAME];
			$this->_assignments['controller'] = $this->_names['layout-name'];
			$this->_assignments['nocache'] = false;
			$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_LARGE';
			//
			// Is it fluid?.
			$opt = $this->_options->option(self::OPTION_FLUID);
			$this->_assignments['containerFluid'] = $opt->activated();
			//
			// Type.
			$opt = $this->_options->option(self::OPTION_CACHED);
			if($opt->activated()) {
				switch($opt->value()) {
					case 'double':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_DOUBLE';
						break;
					case 'large':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_LARGE';
						break;
					case 'medium':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_MEDIUM';
						break;
					case 'small':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_SMALL';
						break;
					case 'NOCACHE':
						$this->_assignments['cached'] = 'false';
						$this->_assignments['nocache'] = true;
						break;
					case 'large':
					default:
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::EXPIRATION_SIZE_LARGE';
				}
			}
			//
			// Checking bootstrap option.
			$opt = $this->_options->option(self::OPTION_TYPE);
			if($opt->activated() && $opt->value() == self::TYPE_BOOTSTRAP) {
				$this->_assignments['name_nav'] = $this->_names['name-nav'];
				$this->_assignments['controller_nav'] = $this->_names['layout-name-nav'];
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
			global $Paths;

			$path = Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONFIGS]}/config_http.php");
			$this->_requiredDirectories[] = dirname($path);

			if(!isset($this->_configLines[$path])) {
				$this->_configLines[$path] = [];
			}

			$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'lib:jquery/jquery-2.1.3.min.js';";

			if(isset($this->_names['templates-type']) && $this->_names['templates-type'] == self::TYPE_BOOTSTRAP) {
				$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_STYLES][] = 'lib:bootstrap/css/bootstrap.min.css';";
				$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_STYLES][] = 'lib:bootstrap/css/bootstrap-theme.min.css';";

				$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'lib:bootstrap/js/bootstrap.min.js';";
			}

			$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_STYLES][] = 'style';";

			$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'toobasic_asset';";
			$this->_configLines[$path][] = "\$Defaults[GC_DEFAULTS_HTMLASSETS][GC_DEFAULTS_HTMLASSETS_SCRIPTS][] = 'script';";
		}
	}
	protected function genNames() {
		if($this->_names === false) {
			parent::genNames();
			//
			// Global dependencies.
			global $Paths;

			$this->_names['layout-name'] = Names::ControllerClass($this->_names[GC_AFIELD_NAME]);
			$this->_names['templates-prefix'] = '';
			//
			// Checking bootstrap option.
			$serparatedBSNav = false;
			$opt = $this->_options->option(self::OPTION_TYPE);
			if($opt->activated()) {
				$this->_names['templates-type'] = $opt->value();
				switch($opt->value()) {
					case self::TYPE_TABLE:
						$this->_names['templates-prefix'] = 'table/';
						break;
					case self::TYPE_BOOTSTRAP:
						$this->_names['templates-prefix'] = 'bs/';
						$serparatedBSNav = true;
						break;
					case self::TYPE_BASIC:
					default:
						$this->_names['templates-prefix'] = '/';
						$this->_names['templates-type'] = self::TYPE_BASIC;
				}
			}
			//
			// Files.
			$this->_files[] = [
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names[GC_AFIELD_NAME]}.php"),
				GC_AFIELD_TEMPLATE => 'controller.html',
				GC_AFIELD_DESCRIPTION => 'controller file'
			];
			$this->_files[] = [
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names[GC_AFIELD_NAME]}.html"),
				GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}view.html",
				GC_AFIELD_DESCRIPTION => 'view file'
			];
			$this->_files[] = [
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_JS]}/script.js"),
				GC_AFIELD_TEMPLATE => 'script.html',
				GC_AFIELD_DESCRIPTION => 'basic JS script file'
			];
			$this->_files[] = [
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CSS]}/style.css"),
				GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}style.html",
				GC_AFIELD_DESCRIPTION => 'basic CSS file'
			];
			if($serparatedBSNav) {
				$this->_names['name-nav'] = "{$this->_names[GC_AFIELD_NAME]}_nav";
				$this->_names['layout-name-nav'] = Names::ControllerClass($this->_names['name-nav']);

				$this->_files[] = [
					GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['name-nav']}.php"),
					GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}controller_nav.html",
					GC_AFIELD_DESCRIPTION => 'nav controller file'
				];
				$this->_files[] = [
					GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['name-nav']}.html"),
					GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}view_nav.html",
					GC_AFIELD_DESCRIPTION => 'nav view file'
				];
			}
		}
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your layouts and create them with a basic structure.');

		parent::setOptions();

		$text = 'Allows you to create a new layout and deploy it in your site.';
		$this->_options->option(self::OPTION_CREATE)->setHelpText($text, 'layout-name');

		$text = 'Allows you to eliminate a layout and its artifacts from your site.';
		$this->_options->option(self::OPTION_REMOVE)->setHelpText($text, 'layout-name');

		$text = "This options allows you to choose a initial structure for your new layout. Options are:\n";
		$text.= "\t- 'basic' (the default)\n";
		$text.= "\t- 'bootstrap'\n";
		$text.= "\t- 'table'\n";
		$text.= "\t- other values are considered 'basic'.";
		$this->_options->addOption(Option::EasyFactory(self::OPTION_TYPE, ['--type', '-t'], Option::TYPE_VALUE, $text, 'value'));

		$text = 'When using Twitter Bootstrap, main containers are fluid.';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FLUID, ['--fluid', '-f'], Option::TYPE_NO_VALUE, $text));

		$text = 'This options allows to set how long a cache entry should be kept for it. ';
		$text.= 'Options are: double, large, medium, small, NOCACHE';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_CACHED, ['--cached', '-c'], Option::TYPE_VALUE, $text, 'delay-size'));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating layout '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing layout '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskRemove($spacer);
	}
}
