<?php

use TooBasic\Shell\Color as TBS_Color;
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

class ControllerSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionCached = 'Cached';
	const OptionCreate = 'Create';
	const OptionLayout = 'Layout';
	const OptionModule = 'Module';
	const OptionParam = 'Param';
	const OptionRemove = 'Remove';
	//
	// Protected properties.
	protected $_render = false;
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function addRoute($newRoute, $path, &$error, &$fatal) {
		$ok = true;

		$backup = false;
		$fatal = false;
		$error = '';

		if(!is_file($path)) {
			if(!file_put_contents($path, '{"routes":[]}')) {
				$ok = false;
				$error = "unable to create file '{$path}'";
				$fatal = true;
			}
		}
		$config = false;
		if($ok) {
			$backup = file_get_contents($path);
			$config = json_decode($backup);
			if(!$config) {
				$ok = false;
				$error = "unable to use routes file";
				$fatal = true;
			}
		}
		if($ok) {
			foreach($config->routes as $route) {
				if($route->action == $newRoute->action) {
					$ok = false;
					$error = "there's another rule for this controller";
					break;
				}
			}
		}
		if($ok) {
			$config->routes[] = $newRoute;
			if(!file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT))) {
				$ok = false;
				$error = "something went wrong writing back routes file";
				$fatal = true;
				file_put_contents($path, $backup);
			}
		}

		return $ok;
	}
	protected function genAssignments($names) {
		//
		// Default values.
		$assignments = array();
		//
		// Assignments.
		$assignments['name'] = $names['name'];
		$assignments['method'] = 'GET';
		$assignments['controller'] = $names['controller-name'];
		$assignments['init'] = true;
		$assignments['nocache'] = false;
		$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';
		$opt = $this->_options->option(self::OptionCached);
		if($opt->activated()) {
			switch($opt->value()) {
				case 'double':
					$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeDouble';
					break;
				case 'large':
					$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';
					break;
				case 'medium':
					$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeMedium';
					break;
				case 'small':
					$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeSmall';
					break;
				case 'NOCACHE':
					$assignments['cached'] = 'false';
					$assignments['nocache'] = true;
					break;
				case 'large':
				default:
					$assignments['cached'] = '\TooBasic\CacheAdapter::ExpirationSizeLarge';
			}
		}
		$assignments['layout'] = false;
		$opt = $this->_options->option(self::OptionLayout);
		if($opt->activated()) {
			if($opt->value() == 'NOLAYOUT') {
				$assignments['layout'] = 'false';
			} else {
				$assignments['layout'] = "'{$opt->value()}'";
			}
		}
		$opt = $this->_options->option(self::OptionParam);
		if($opt->activated()) {
			$assignments['cache_params'] = $opt->value();
			$assignments['required_params'] = $opt->value();
		} else {
			$assignments['cache_params'] = array();
			$assignments['required_params'] = array();
		}

		return $assignments;
	}
	protected function genControllerFile($names, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Forcing render to be loaded.
		$this->loadRender();
		//
		// Assignments.
		$assignments = $this->genAssignments($names);
		//
		// Generating file content.
		$output = $this->_render->render($assignments, "skeletons/controller.html");

		$result = file_put_contents($names['controller-path'], $output);
		if($result === false) {
			$error = "Unable to write file '{$names['controller-path']}'";
			$out = false;
		}

		return $out;
	}
	protected function genNames($baseName) {
		//
		// Default values.
		$out = array();
		//
		// Global dependencies.
		global $Directories;
		global $Paths;

		$out['name'] = $baseName;
		$out['module-name'] = false;
		$out['controller-name'] = \TooBasic\classname($baseName).GC_CLASS_SUFFIX_CONTROLLER;

		$opt = $this->_options->option(self::OptionModule);
		if($opt->activated()) {
			$out['module-name'] = $opt->value();
			$out['controller-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$out['module-name']}/{$Paths[GC_PATHS_CONTROLLERS]}/{$baseName}.php");
			$out['view-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$out['module-name']}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$baseName}.html");
			$out['routes-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$out['module-name']}/{$Paths[GC_PATHS_CONFIGS]}/routes.json");
		} else {
			$out['controller-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONTROLLERS]}/{$baseName}.php");
			$out['view-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_TEMPLATES]}/".GC_VIEW_MODE_ACTION."/{$baseName}.html");
			$out['routes-path'] = TB_Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONFIGS]}/routes.json");
		}

		return $out;
	}
	protected function genViewFile($names, &$error) {
		//
		// Default values.
		$out = true;
		$assignments = array();
		//
		// Forcing render to be loaded.
		$this->loadRender();
		//
		// Assignments.
		$assignments['name'] = $names['name'];
		$assignments['controller'] = $names['controller-name'];
		//
		// Generating file content.
		$output = $this->_render->render($assignments, "skeletons/view.html");

		$result = file_put_contents($names['view-path'], $output);
		if($result === false) {
			$error = "Unable to write file '{$names['view-path']}'";
			$out = false;
		}

		return $out;
	}
	protected function loadRender() {
		if(!$this->_render) {
			$this->_render = \TooBasic\Adapter::Factory('\TooBasic\ViewAdapterSmarty');

			$engine = $this->_render->engine();
			$engine->left_delimiter = '<%';
			$engine->right_delimiter = '%>';
		}
	}
	protected function removeRoute($badRoute, $path, &$error, &$fatal) {
		$ok = true;

		$backup = false;
		$fatal = false;
		$error = '';

		if(!is_file($path)) {
			$ok = false;
			$error = "unable to find file '{$path}'";
			$fatal = true;
		}
		$config = false;
		if($ok) {
			$backup = file_get_contents($path);
			$config = json_decode($backup);
			if(!$config) {
				$ok = false;
				$error = "unable to use routes file";
				$fatal = true;
			}
		}
		if($ok) {
			$found = false;
			foreach($config->routes as $routeKey => $route) {
				if($route->action == $badRoute->action) {
					unset($config->routes[$routeKey]);
					$found = true;
				}
			}
			if($found) {
				if(!file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT))) {
					$ok = false;
					$error = "something went wrong writing back routes file";
					$fatal = true;
					file_put_contents($path, $backup);
				}
			} else {
				$ok = false;
				$error = "no routes found";
			}
		}

		return $ok;
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage you controllers.');

		$text = 'Allows you to create a new controller and deploy it in your site.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), TBS_Option::TypeValue, $text, 'controller-name'));

		$text = 'Allows you to eliminate a controller and it\'s view from your site.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), TBS_Option::TypeValue, $text, 'controller-name'));

		$text = 'This options allows to set how long a cache entry should be kept for it. ';
		$text.= 'Options are: double, large, medium, small, NOCACHE';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCached, array('--cached', '-c'), TBS_Option::TypeValue, $text, 'delay-size'));

		$text = 'This options allows to set a specific layout for your controller. ';
		$text.= 'NOLAYOUT means force the controller to work without layout.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionLayout, array('--layout', '-l'), TBS_Option::TypeValue, $text, 'layout-name'));

		$text = 'Adds a param to be use as cache key and url requirement.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionParam, array('--param', '-p'), TBS_Option::TypeMultiValue, $text, 'param-name'));

		$text = 'Generate files inside a module.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionModule, array('--module', '-m'), TBS_Option::TypeValue, $text, 'module-name'));
	}
	protected function taskCreate($spacer = '') {
		$ok = true;
		//
		// Global dependencies.
		global $Defaults;

		$name = $this->_options->option(self::OptionCreate)->value();

		$names = $this->genNames($name);
		echo "{$spacer}Creating controller '{$name}':\n";

		if($ok) {
			echo "{$spacer}\tCreating required directories:\n";
			$dirPaths = array(
				dirname($names['controller-path']),
				dirname($names['view-path']),
				dirname($names['routes-path'])
			);

			foreach($dirPaths as $dirPath) {
				if(!is_dir($dirPath)) {
					echo "{$spacer}\t\tCreating '{$dirPath}': ";
					@mkdir($dirPath, 0777, true);

					if(is_dir($dirPath)) {
						echo TBS_Color::Green('Ok');
					} else {
						echo TBS_Color::Red('Failed');
						$ok = false;
						break;
					}
					echo "\n";
				}
			}
		}

		if($ok) {
			echo "{$spacer}\tCreating controller file: ";
			if(is_file($names['controller-path'])) {
				echo TBS_Color::Yellow('Ignored').' (controller already exists)';
			} else {
				$error = false;
				if($this->genControllerFile($names, $error)) {
					echo TBS_Color::Green('Ok');
					echo "\n{$spacer}\t\t'{$names['controller-path']}'";
				} else {
					echo TBS_Color::Red('Failed')." ({$error})";
					$ok = false;
				}
			}
			echo "\n";
		}

		if($ok) {
			echo "{$spacer}\tCreating view file: ";
			if(is_file($names['view-path'])) {
				echo TBS_Color::Yellow('Ignored').' (view already exists)';
			} else {
				$error = false;
				if($this->genViewFile($names, $error)) {
					echo TBS_Color::Green('Ok');
					echo "\n{$spacer}\t\t'{$names['view-path']}'";
				} else {
					echo TBS_Color::Red('Failed')." ({$error})";
					$ok = false;
				}
			}
			echo "\n";
		}

		if($ok && $Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			echo "{$spacer}\tAdding route configuration: ";

			$route = new \stdClass();
			$route->route = $name;
			$opt = $this->_options->option(self::OptionParam);
			if($opt->activated()) {
				foreach($opt->value() as $param) {
					$route->route .= "/:{$param}:";
				}
			}
			$route->action = $name;

			$error = '';
			$fatal = false;
			if($this->addRoute($route, $names['routes-path'], $error, $fatal)) {
				echo TBS_Color::Green('Ok');
				echo "\n{$spacer}\t\t'{$names['routes-path']}'";
			} else {
				if($fatal) {
					echo TBS_Color::Red('Failed');
					$ok = false;
				} else {
					echo TBS_Color::Yellow('Ignored');
				}
				echo " ({$error})";
			}
			echo "\n";
		}
	}
	protected function taskInfo($spacer = '') {
		
	}
	protected function taskRemove($spacer = '') {
		$name = $this->_options->option(self::OptionRemove)->value();
		$ok = true;
		//
		// Global dependencies.
		global $Defaults;

		$names = $this->genNames($name);

		echo "{$spacer}Removing controller '{$name}':\n";

		echo "{$spacer}\tRemoving controller file: ";
		if(!is_file($names['controller-path'])) {
			echo TBS_Color::Yellow('Ignored').' (controller already removed)';
		} else {
			@unlink($names['controller-path']);
			if(!is_file($names['controller-path'])) {
				echo TBS_Color::Green('Ok');
				echo "\n{$spacer}\t\t'{$names['controller-path']}'";
			} else {
				echo TBS_Color::Red('Failed')." (unable to remove it)";
				$ok = false;
			}
		}
		echo "\n";

		echo "{$spacer}\tRemoving view file: ";
		if(!is_file($names['view-path'])) {
			echo TBS_Color::Yellow('Ignored').' (view already removed)';
		} else {
			@unlink($names['view-path']);
			if(!is_file($names['view-path'])) {
				echo TBS_Color::Green('Ok');
				echo "\n{$spacer}\t\t'{$names['view-path']}'";
			} else {
				echo TBS_Color::Red('Failed')." (unable to remove it)";
				$ok = false;
			}
		}
		echo "\n";

		if($ok && $Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			echo "{$spacer}\tRemoving route configuration: ";

			$route = new \stdClass();
			$route->action = $name;

			$error = '';
			$fatal = false;
			if($this->removeRoute($route, $names['routes-path'], $error, $fatal)) {
				echo TBS_Color::Green('Ok');
				echo "\n{$spacer}\t\t'{$names['routes-path']}'";
			} else {
				if($fatal) {
					echo TBS_Color::Red('Failed');
					$ok = false;
				} else {
					echo TBS_Color::Yellow('Ignored');
				}
				echo " ({$error})";
			}
			echo "\n";
		}
	}
}
