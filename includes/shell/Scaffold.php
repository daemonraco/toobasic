<?php

namespace TooBasic\Shell;

use TooBasic\Shell\Color as TBS_Color;
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

abstract class Scaffold extends ShellTool {
	//
	// Constants.
	const OptionCreate = 'Create';
	const OptionForced = 'Forced';
	const OptionModule = 'Module';
	const OptionRemove = 'Remove';
	//
	// Protected properties.
	protected $_assignments = false;
	protected $_files = array();
	protected $_forced = null;
	protected $_names = false;
	protected $_render = false;
	protected $_requiredDirectories = array();
	protected $_routes = false;
	protected $_scaffoldName = '';
	//
	// Protected methods.
	protected function addAllRoutes($spacer) {
		//
		// Default values-
		$ok = true;
		//
		// Global dependencies.
		global $Defaults;

		if($ok && $Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			$this->genRoutes();

			if(count($this->_routes)) {
				echo "{$spacer}Adding routes configuration:\n";
				foreach($this->_routes as $route) {
					echo "{$spacer}\t- '{$route->route}': ";

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
			}
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
	protected function enforceFilesList() {
		foreach($this->_files as &$file) {
			if(!isset($file['description'])) {
				$file['description'] = "file '".basename($file['path'])."'";
			}
			if(!isset($file['generator'])) {
				$file['generator'] = 'genFileByTemplate';
			}
			if(!isset($file['template'])) {
				$file['template'] = false;
			}

			$this->_requiredDirectories[] = dirname($file['path']);
		}
	}
	protected function genAssignments() {
		$this->genNames();
		if($this->_assignments === false) {
			//
			// Default values.
			$this->_assignments = array();
		}
	}
	protected function genFile($path, $template = false, $callback = 'genFileByTemplate') {
		//
		// Default values.
		$ok = true;

		if(!$this->isForced() && is_file($path)) {
			echo TBS_Color::Yellow('Ignored').' (file already exist)';
		} else {
			$completeTemplate = TB_Sanitizer::DirPath("scaffolds/{$this->_scaffoldName}/{$template}");

			$error = false;
			if($this->{$callback}($path, $completeTemplate, $error)) {
				echo TBS_Color::Green('Ok');
			} else {
				echo TBS_Color::Red('Failed')." ({$error})";
				$ok = false;
			}
		}
		echo "\n";

		return $ok;
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
		//
		// Cleaning directories list.
		$this->_requiredDirectories = array_unique($this->_requiredDirectories);
		//
		// Checking which directories have to be created.
		$toGen = array();
		foreach($this->_requiredDirectories as $dirPath) {
			if(!is_dir($dirPath)) {
				$toGen[] = $dirPath;
			}
		}
		//
		// Is there something to create.
		if($toGen) {
			echo "{$spacer}\tCreating required directories:\n";
			//
			// Creating each directory.
			foreach($toGen as $dirPath) {
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
	protected function genRoutes() {
		if($this->_routes === false) {
			$this->_routes = array();
		}
	}
	protected function isForced() {
		if($this->_forced === null) {
			$this->_forced = $this->_options->option(self::OptionForced)->activated();
		}

		return $this->_forced;
	}
	protected function loadRender() {
		if(!$this->_render) {
			$this->_render = \TooBasic\Adapter::Factory('\TooBasic\ViewAdapterSmarty');

			$engine = $this->_render->engine();
			$engine->left_delimiter = '<%';
			$engine->right_delimiter = '%>';
			$engine->force_compile = true;
		}
	}
	protected function removeAllRoutes($spacer) {
		//
		// Default values-
		$ok = true;
		//
		// Global dependencies.
		global $Defaults;

		if($ok && $Defaults[GC_DEFAULTS_ALLOW_ROUTES]) {
			$this->genRoutes();

			if(count($this->_routes)) {
				echo "{$spacer}Removing routes configuration:\n";
				foreach($this->_routes as $route) {
					echo "{$spacer}\t- '{$route->route}': ";

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
			}
		}

		return $ok;
	}
	protected function removeFile($path) {
		//
		// Default values.
		$ok = true;

		if(!is_file($path)) {
			echo TBS_Color::Yellow('Ignored').' (file already removed)';
		} else {
			@unlink($path);
			if(!is_file($path)) {
				echo TBS_Color::Green('Ok');
			} else {
				echo TBS_Color::Red('Failed')." (unable to remove it)";
				$ok = false;
			}
		}
		echo "\n";

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

		$text = 'Overwrite files when they exist (routes are excluded).';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionForced, array('--forced'), TBS_Option::TypeNoValue, $text));
	}
	protected function taskCreate($spacer = "") {
		//
		// Default values.
		$ok = true;
		//
		// Enforcing names generation.
		$this->genNames();
		//
		// Enforcing files list structure.
		$this->enforceFilesList();
		//
		// Directories
		if($ok) {
			$ok = $this->genRequiredDirectories($spacer);
		}
		//
		// Files
		if($ok) {
			foreach($this->_files as $file) {
				echo "{$spacer}\tCreating {$file['description']}: ";
				if(!$this->genFile($file['path'], $file['template'], $file['generator'])) {
					$ok = false;
				}
				echo "{$spacer}\t\t- '{$file['path']}'\n\n";

				if(!$ok) {
					break;
				}
			}
		}
		//
		// Routes.
		if($ok) {
			$ok = $this->addAllRoutes("{$spacer}\t");
		}

		return $ok;
	}
	protected function taskRemove($spacer = "") {
		//
		// Default values.
		$ok = true;
		//
		// Enforcing names generation.
		$this->genNames();
		//
		// Enforcing files list structure.
		$this->enforceFilesList();
		//
		// Files
		if($ok) {
			foreach($this->_files as $file) {
				echo "{$spacer}\tRemoving {$file['description']}: ";
				if(!$this->removeFile($file['path'])) {
					$ok = false;
				}
				echo "{$spacer}\t\t- '{$file['path']}'\n\n";

				if(!$ok) {
					break;
				}
			}
		}
		//
		// Routes.
		if($ok) {
			$ok = $this->removeAllRoutes("{$spacer}\t");
		}

		return $ok;
	}
}
