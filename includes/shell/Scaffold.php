<?php

namespace TooBasic\Shell;

use TooBasic\Shell\Color as TBS_Color;
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

abstract class Scaffold extends ShellTool {
	//
	// Constants.
	const OptionCreate = 'Create';
	const OptionModule = 'Module';
	const OptionRemove = 'Remove';
	//
	// Protected properties.
	protected $_assignments = false;
	protected $_names = false;
	protected $_render = false;
	protected $_requiredDirectories = array();
	//
	// Protected methods.
	protected function addAllRoutes($spacer) {
		$ok = true;

		$this->genRoutes();
		foreach($this->_routes as $route) {
			echo "{$spacer}-'{$route->route}': ";

			$error = '';
			$fatal = false;
			if($this->addRoute($route, $error, $fatal)) {
				echo TBS_Color::Green('Ok');
			} else {
				if($fatal) {
					echo TBS_Color::Red('Failed');
					$ok = false;
					break;
				} else {
					echo TBS_Color::Yellow('Ignored');
				}
				echo " ({$error})";
			}
			echo "\n";
		}

		return $ok;
	}
	protected function addRoute(\stdClass $newRoute, &$error, &$fatal) {
		$ok = true;

		$backup = false;
		$fatal = false;
		$error = '';

		if(!is_file($this->_names['routes-path'])) {
			if(!file_put_contents($this->_names['routes-path'], '{"routes":[]}')) {
				$ok = false;
				$error = "unable to create file '{$this->_names['routes-path']}'";
				$fatal = true;
			}
		}
		$config = false;
		if($ok) {
			$backup = file_get_contents($this->_names['routes-path']);
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

			if(!file_put_contents($this->_names['routes-path'], json_encode($config, JSON_PRETTY_PRINT))) {
				$ok = false;
				$error = "something went wrong writing back routes file";
				$fatal = true;
				file_put_contents($this->_names['routes-path'], $backup);
			}
		}

		return $ok;
	}
	protected function genAssignments() {
		$this->genNames();
		if($this->_assignments === false) {
			//
			// Default values.
			$this->_assignments = array();
		}
	}
	protected function genFileByTemplate($path, $template, &$error) {
		//
		// Default values.
		$out = true;
		//
		// Forcing render to be loaded.
		$this->loadRender();
		//
		// Assignments.
		$this->genAssignments();
		//
		// Generating file content.
		$output = $this->_render->render($this->_assignments, $template);

		$result = file_put_contents($path, $output);
		if($result === false) {
			$error = "Unable to write file '{$path}'";
			$out = false;
		}

		return $out;
	}
	protected function genNames() {
		if($this->_names === false) {
			//
			// Default values.
			$this->_names = array();
			//
			// Base name.
			$baseName = '';
			$cOpt = $this->_options->option(self::OptionCreate);
			$rOpt = $this->_options->option(self::OptionRemove);
			if($cOpt->activated()) {
				$baseName = $cOpt->value();
			} elseif($rOpt->activated()) {
				$baseName = $rOpt->value();
			}
			//
			// Global dependencies.
			global $Directories;
			global $Paths;

			$this->_names['name'] = $baseName;
			$this->_names['module-name'] = false;
			$this->_names['parent-directory'] = false;
			//
			// Checking module and parent directory.
			$opt = $this->_options->option(self::OptionModule);
			if($opt->activated()) {
				$this->_names['module-name'] = $opt->value();
				$this->_names['parent-directory'] = "{$Directories[GC_DIRECTORIES_MODULES]}/{$this->_names['module-name']}";
			} else {
				$this->_names['parent-directory'] = $Directories[GC_DIRECTORIES_SITE];
			}

			$this->_names['routes-path'] = TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_CONFIGS]}/routes.json");
			$this->_requiredDirectories[] = dirname($this->_names['routes-path']);
		}
	}
	protected function genRequiredDirectories($spacer) {
		$ok = true;

		echo "{$spacer}\tCreating required directories:\n";

		$this->_requiredDirectories = array_unique($this->_requiredDirectories);
		foreach($this->_requiredDirectories as $dirPath) {
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

		return $ok;
	}
	abstract protected function genRoutes();
	protected function loadRender() {
		if(!$this->_render) {
			$this->_render = \TooBasic\Adapter::Factory('\TooBasic\ViewAdapterSmarty');

			$engine = $this->_render->engine();
			$engine->left_delimiter = '<%';
			$engine->right_delimiter = '%>';
		}
	}
	protected function removeAllRoutes($spacer) {
		$ok = true;

		$this->genRoutes();
		foreach($this->_routes as $route) {
			echo "{$spacer}-'{$route->route}': ";

			$error = '';
			$fatal = false;
			if($this->removeRoute($route, $error, $fatal)) {
				echo TBS_Color::Green('Ok');
			} else {
				if($fatal) {
					echo TBS_Color::Red('Failed');
					$ok = false;
					break;
				} else {
					echo TBS_Color::Yellow('Ignored');
				}
				echo " ({$error})";
			}
			echo "\n";
		}

		return $ok;
	}
	protected function removeRoute(\stdClass $badRoute, &$error, &$fatal) {
		$ok = true;

		$backup = false;
		$fatal = false;
		$error = '';

		if(!is_file($this->_names['routes-path'])) {
			$ok = false;
			$error = "unable to find file '{$this->_names['routes-path']}'";
			$fatal = true;
		}
		$config = false;
		if($ok) {
			$backup = file_get_contents($this->_names['routes-path']);
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
				$config->routes = array_values($config->routes);
				if(!file_put_contents($this->_names['routes-path'], json_encode($config, JSON_PRETTY_PRINT))) {
					$ok = false;
					$error = "something went wrong writing back routes file";
					$fatal = true;
					file_put_contents($this->_names['routes-path'], $backup);
				}
			} else {
				$ok = false;
				$error = "no routes found";
			}
		}

		return $ok;
	}
	protected function setOptions() {
		$text = 'Use: $this->_options->option(self::OptionCreate)->setHelpText(\'text\', \'valueName\');';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), TBS_Option::TypeValue, $text, 'name'));

		$text = 'Use: $this->_options->option(self::OptionRemove)->setHelpText(\'text\', \'valueName\');';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), TBS_Option::TypeValue, $text, 'name'));

		$text = 'Generate files inside a module.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionModule, array('--module', '-m'), TBS_Option::TypeValue, $text, 'name'));
	}
	abstract protected function taskCreate($spacer = "");
	abstract protected function taskRemove($spacer = "");
}
