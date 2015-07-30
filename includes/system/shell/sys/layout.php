<?php

/**
 * @file layout.php
 * @author Alejandro Dario Simi
 */
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

/**
 * @class LayoutSystool
 */
class LayoutSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OptionCached = 'Cached';
	const OptionFluid = 'Fluid';
	const OptionName = 'Name';
	const OptionType = 'Type';
	const TypeBasic = 'basic';
	const TypeBootstrap = 'bootstrap';
	const TypeTable = 'table';
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
			$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeLarge';
			//
			// Is it fluid?.
			$opt = $this->_options->option(self::OptionFluid);
			$this->_assignments['containerFluid'] = $opt->activated();
			//
			// Type.
			$opt = $this->_options->option(self::OptionCached);
			if($opt->activated()) {
				switch($opt->value()) {
					case 'double':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeDouble';
						break;
					case 'large':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeLarge';
						break;
					case 'medium':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeMedium';
						break;
					case 'small':
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeSmall';
						break;
					case 'NOCACHE':
						$this->_assignments['cached'] = 'false';
						$this->_assignments['nocache'] = true;
						break;
					case 'large':
					default:
						$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeLarge';
				}
			}
		}
	}
	protected function genNames() {
		if($this->_names === false) {
			parent::genNames();
			//
			// Global dependencies.
			global $Paths;

			$this->_names['layout-name'] = \TooBasic\classname($this->_names[GC_AFIELD_NAME]).GC_CLASS_SUFFIX_CONTROLLER;
			$this->_names['templates-prefix'] = '';
			//
			// Checking bootstrap option.
			$opt = $this->_options->option(self::OptionType);
			if($opt->activated()) {
				switch($opt->value()) {
					case self::TypeTable:
						$this->_names['templates-prefix'] = 'table/';
						break;
					case self::TypeBootstrap:
						$this->_names['templates-prefix'] = 'bs/';
						break;
					case self::TypeBasic:
					default:
						$this->_names['templates-prefix'] = 'table/';
				}
			}
			//
			// Files.
			$this->_files[] = array(
				GC_AFIELD_PATH => TB_Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names[GC_AFIELD_NAME]}.php"),
				GC_AFIELD_TEMPLATE => 'controller.html',
				GC_AFIELD_DESCRIPTION => 'controller file'
			);
			$this->_files[] = array(
				GC_AFIELD_PATH => TB_Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names[GC_AFIELD_NAME]}.html"),
				GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}view.html",
				GC_AFIELD_DESCRIPTION => 'view file'
			);
			$this->_files[] = array(
				GC_AFIELD_PATH => TB_Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_JS]}/script.js"),
				GC_AFIELD_TEMPLATE => 'script.html',
				GC_AFIELD_DESCRIPTION => 'basic JS script file'
			);
			$this->_files[] = array(
				GC_AFIELD_PATH => TB_Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CSS]}/style.css"),
				GC_AFIELD_TEMPLATE => "{$this->_names['templates-prefix']}style.html",
				GC_AFIELD_DESCRIPTION => 'basic CSS file'
			);
		}
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your layouts and create them with a basic structure.');

		parent::setOptions();

		$text = 'Allows you to create a new layout and deploy it in your site.';
		$this->_options->option(self::OptionCreate)->setHelpText($text, 'layout-name');

		$text = 'Allows you to eliminate a layout and its artifacts from your site.';
		$this->_options->option(self::OptionRemove)->setHelpText($text, 'layout-name');

		$text = "TODO help text for: '--name', '-n'.";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionName, array('--name', '-n'), TBS_Option::TypeValue, $text, 'value'));

		$text = "This options allows you to choose a initial structure for your new layout. Options are:\n";
		$text.= "\t- 'basic' (the default)\n";
		$text.= "\t- 'bootstrap'\n";
		$text.= "\t- 'table'\n";
		$text.= "\t- other values are considered 'basic'.\n";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionType, array('--type', '-t'), TBS_Option::TypeValue, $text, 'value'));

		$text = 'When using Twitter Bootstrap, main containers are fluid.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionFluid, array('--fluid', '-f'), TBS_Option::TypeNoValue, $text));

		$text = 'This options allows to set how long a cache entry should be kept for it. ';
		$text.= 'Options are: double, large, medium, small, NOCACHE';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCached, array('--cached', '-c'), TBS_Option::TypeValue, $text, 'delay-size'));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating layout '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskInfo($spacer = '') {
		
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing layout '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskRemove($spacer);
	}
}
