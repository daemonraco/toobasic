<?php

/**
 * @file service.php
 * @author Alejandro Dario Simi
 */
//
// Class aliases.
use TooBasic\Names;
use TooBasic\Sanitizer;
use TooBasic\Shell\Option;

/**
 * @class ServiceSystool
 */
class ServiceSystool extends TooBasic\Shell\ExporterScaffold {
	//
	// Constants.
	const OptionCached = 'Cached';
	//
	// Protected properties.
	protected $_genRoutePrefix = 'srv/';
	protected $_genRouteType = 'service';
	protected $_scaffoldName = 'service';
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
			$this->_assignments['service'] = $this->_names['service-name'];
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

			$opt = $this->_options->option(self::OptionParam);
			$this->_assignments['cache_params'] = [];
			$this->_assignments['required_params'] = [];
			if($opt->activated()) {
				foreach($opt->value() as $param) {
					$param = explode(':', $param);
					$param = array_shift($param);

					$this->_assignments['cache_params'][] = $param;
					$this->_assignments['required_params'][] = $param;
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

			$this->_names['service-name'] = Names::ServiceClass($this->_names[GC_AFIELD_NAME]);
			//
			// Files.
			$this->_files[] = [
				GC_AFIELD_PATH => Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_SERVICES]}/{$this->_names[GC_AFIELD_NAME]}.php"),
				GC_AFIELD_TEMPLATE => 'service.html',
				GC_AFIELD_DESCRIPTION => 'service file'
			];
		}
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your services.');

		parent::setOptions();

		$text = 'Allows you to create a new service and deploy it in your site.';
		$this->_options->option(self::OptionCreate)->setHelpText($text, 'service-name');

		$text = 'Allows you to eliminate a service from your site.';
		$this->_options->option(self::OptionRemove)->setHelpText($text, 'service-name');

		$text = 'This options allows to set how long a cache entry should be kept for it. ';
		$text.= 'Options are: double, large, medium, small, NOCACHE';
		$this->_options->addOption(Option::EasyFactory(self::OptionCached, ['--cached', '-c'], Option::TypeValue, $text, 'delay-size'));

		$text = 'Adds a param to be use as cache key and url requirement.';
		$this->_options->addOption(Option::EasyFactory(self::OptionParam, ['--param', '-p'], Option::TypeMultiValue, $text, 'param-name'));
	}
	protected function taskCreate($spacer = '') {
		$this->genNames();

		echo "{$spacer}Creating service '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskCreate($spacer);
	}
	protected function taskRemove($spacer = '') {
		$this->genNames();

		echo "{$spacer}Removing service '{$this->_names[GC_AFIELD_NAME]}':\n";

		return parent::taskRemove($spacer);
	}
}
