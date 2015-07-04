<?php

use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

class ControllerSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const OptionCached = 'Cached';
	const OptionLayout = 'Layout';
	const OptionParam = 'Param';
	//
	// Protected properties.
	protected $_scaffoldName = 'controller';
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments() {
		if($this->_assignments === false) {
			parent::genAssignments();
			//
			// Assignments.
			$this->_assignments['name'] = $this->_names['name'];
			$this->_assignments['method'] = 'GET';
			$this->_assignments['controller'] = $this->_names['controller-name'];
			$this->_assignments['init'] = true;
			$this->_assignments['nocache'] = false;
			$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';

			$opt = $this->_options->option(self::OptionCached);
			if($opt->activated()) {
				switch($opt->value()) {
					case 'double':
						$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeDouble';
						break;
					case 'large':
						$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';
						break;
					case 'medium':
						$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeMedium';
						break;
					case 'small':
						$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeSmall';
						break;
					case 'NOCACHE':
						$this->_assignments['cached'] = 'false';
						$this->_assignments['nocache'] = true;
						break;
					case 'large':
					default:
						$this->_assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';
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

			$this->_names['controller-name'] = \TooBasic\classname($this->_names['name']).GC_CLASS_SUFFIX_CONTROLLER;
			//
			// Files.
			$this->_files[] = array(
				'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$this->_names['name']}.php"),
				'template' => 'controller.html',
				'description' => 'controller file'
			);
			$this->_files[] = array(
				'path' => TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$this->_names['name']}.html"),
				'template' => 'view.html',
				'description' => 'view file'
			);
		}
	}
	protected function genRoutes() {
		if($this->_routes === false) {
			parent::genRoutes();
			//
			// Controller's route.
			$route = new \stdClass();
			$route->route = $this->_names['name'];
			$opt = $this->_options->option(self::OptionParam);
			if($opt->activated()) {
				foreach($opt->value() as $param) {
					$route->route .= "/:{$param}:";
				}
			}
			$route->action = $this->_names['name'];
			$this->_routes[] = $route;
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
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCached, array('--cached', '-c'), TBS_Option::TypeValue, $text, 'delay-size'));

		$text = 'This options allows to set a specific layout for your controller. ';
		$text.= 'NOLAYOUT means force the controller to work without layout.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionLayout, array('--layout', '-l'), TBS_Option::TypeValue, $text, 'layout-name'));

		$text = 'Adds a param to be use as cache key and url requirement.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionParam, array('--param', '-p'), TBS_Option::TypeMultiValue, $text, 'param-name'));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating controller '{$this->_names['name']}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskInfo($spacer = '') {
		
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing controller '{$this->_names['name']}':\n";

		return parent::taskRemove($spacer);
	}
}
