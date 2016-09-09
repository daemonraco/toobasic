<?php

/**
 * @file controller.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Names;
use TooBasic\Sanitizer;
use TooBasic\Shell\Option;

/**
 * @class ControllerSystool
 */
class ControllerSystool extends TooBasic\Shell\ExporterScaffold {
	//
	// Constants.
	const OptionCached = 'Cached';
	const OptionLayout = 'Layout';
	//
	// Protected properties.
	protected $_genRoutePrefix = '';
	protected $_genRouteType = 'action';
	protected $_scaffoldName = 'controller';
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments() {
		if($this->_assignments === false) {
			parent::genAssignments();
			//
			// Assignments.
			$this->_assignments['name'] = $this->_names[GC_AFIELD_NAME];
			$this->_assignments['method'] = 'GET';
			$this->_assignments['controller'] = $this->_names['controller-name'];
			$this->_assignments['init'] = true;
			$this->_assignments['nocache'] = false;
			$this->_assignments['cached'] = '\\TooBasic\\Adapters\\Cache\\Adapter::ExpirationSizeLarge';

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

			$this->_assignments['layout'] = false;
			$opt = $this->_options->option(self::OptionLayout);
			if($opt->activated()) {
				if($opt->value() == 'NOLAYOUT') {
					$this->_assignments['layout'] = 'false';
				} else {
					$this->_assignments['layout'] = "'{$opt->value()}'";
				}
			}

			$opt = $this->_options->option(self::OptionParam);
			if($opt->activated()) {
				$this->_assignments['cache_params'] = $opt->value();
				$this->_assignments['required_params'] = $opt->value();
			} else {
				$this->_assignments['cache_params'] = array();
				$this->_assignments['required_params'] = array();
			}
		}
	}
	protected function genNames() {
		if($this->_names === false) {
			parent::genNames();
			//
			// Global dependencies.
			global $Paths;

			$this->_names['controller-name'] = Names::ControllerClass($this->_names[GC_AFIELD_NAME]);
			//
			// Files.
			$this->_files[] = array(
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names[GC_AFIELD_NAME]}.php"),
				GC_AFIELD_TEMPLATE => 'controller.html',
				GC_AFIELD_DESCRIPTION => 'controller file'
			);
			$this->_files[] = array(
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names[GC_AFIELD_NAME]}.html"),
				GC_AFIELD_TEMPLATE => 'view.html',
				GC_AFIELD_DESCRIPTION => 'view file'
			);
		}
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your controllers.');

		parent::setOptions();

		$text = 'Allows you to create a new controller and deploy it in your site.';
		$this->_options->option(self::OptionCreate)->setHelpText($text, 'controller-name');

		$text = 'Allows you to eliminate a controller and its view from your site.';
		$this->_options->option(self::OptionRemove)->setHelpText($text, 'controller-name');

		$text = 'This options allows to set how long a cache entry should be kept for it. ';
		$text.= 'Options are: double, large, medium, small, NOCACHE';
		$this->_options->addOption(Option::EasyFactory(self::OptionCached, array('--cached', '-c'), Option::TypeValue, $text, 'delay-size'));

		$text = 'This options allows to set a specific layout for your controller. ';
		$text.= 'NOLAYOUT means force the controller to work without layout.';
		$this->_options->addOption(Option::EasyFactory(self::OptionLayout, array('--layout', '-l'), Option::TypeValue, $text, 'layout-name'));

		$text = 'Adds a param to be use as cache key and url requirement.';
		$this->_options->addOption(Option::EasyFactory(self::OptionParam, array('--param', '-p'), Option::TypeMultiValue, $text, 'param-name'));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating controller '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing controller '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskRemove($spacer);
	}
}
