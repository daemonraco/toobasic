<?php

use TooBasic\Shell\Color as TBS_Color;
use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

class ShellSystool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const ErrorOk = 0;
	const ErrorType = 1;
	const ErrorParameters = 2;
	const OptionCreate = 'Create';
	const OptionMasterParam = 'MasterParam';
	const OptionModule = 'Module';
	const OptionParam = 'Param';
	const OptionRemove = 'Remove';
	const OptionType = 'Type';
	const TypeCron = 'cron';
	const TypeSys = 'sys';
	const TypeTool = 'tool';
	//
	// Protected properties.
	protected $_currentType = false;
	protected $_render = false;
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments($names) {
		//
		// Default values.
		$assignments = array();
		//
		// Assignments.
		$assignments['name'] = $names['name'];
		$assignments['tool'] = $names['tool-name'];
		$assignments['toolParent'] = $names['tool-parent'];
		$assignments['options'] = array();
		$assignments['masterOptions'] = array();

		$mpOpt = $this->_options->option(self::OptionMasterParam);
		$mpNames = $mpOpt->activated() ? $mpOpt->value() : array();
		$pOpt = $this->_options->option(self::OptionParam);
		$pNames = array_merge($mpNames, $pOpt->activated() ? $pOpt->value() : array());
		$triggers = array();
		$fullOption = array();

		foreach($pNames as $name) {
			$p = explode(':', $name);
			$p = $p[0];

			$fullName = "--{$p}";
			if(!in_array($fullName, $triggers)) {
				$fullOption[$name] = array(
					$fullName
				);
			} else {
				$this->setError(self::ErrorParameters, "Trigger '{$fullName}' seems to be duplicated");
			}
			$shortName = "-{$p[0]}";
			if(!in_array($shortName, $triggers)) {
				$fullOption[$name][] = $shortName;
			} else {
				$shortName = strtoupper($shortName);
				if(!in_array($shortName, $triggers)) {
					$fullOption[$name][] = $shortName;
				}
			}
			$triggers[] = $fullName;
			$triggers[] = $shortName;
		}

		foreach($fullOption as $name => $optTriggers) {
			$name = explode(':', $name);
			$paramType = 'N';
			if(isset($name[1])) {
				$paramType = strtoupper($name[1]);
			}
			$name = $name[0];

			$aux = array(
				'name' => str_replace(' ', '', ucwords(implode(' ', explode('-', $name)))),
				'triggers' => $optTriggers
			);
			switch($paramType) {
				case 'M':
					$aux['type'] = 'TypeMultiValue';
					break;
				case 'V':
					$aux['type'] = 'TypeValue';
					break;
				case 'N':
				default:
					$aux['type'] = 'TypeNoValue';
					break;
			}

			$assignments['options'][] = $aux;
			if(in_array($name, $mpNames)) {
				$assignments['masterOptions'][] = $aux;
			}
		}

		return $assignments;
	}
	protected function genToolFile($names, &$error) {
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
		$output = $this->_render->render($assignments, "skeletons/tool.html");

		$result = file_put_contents($names['tool-path'], $output);
		if($result === false) {
			$error = "Unable to write file '{$names['tool-path']}'";
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

		$parentDir = '';
		$out['name'] = $baseName;
		$out['type'] = $this->_currentType;
		$out['module-name'] = false;

		$opt = $this->_options->option(self::OptionModule);
		if($opt->activated()) {
			$out['module-name'] = $opt->value();
			$parentDir = "{$Directories[GC_DIRECTORIES_MODULES]}/{$out['module-name']}";
		} else {
			$parentDir = $Directories[GC_DIRECTORIES_SITE];
		}

		$out['name'] = $baseName;
		$out['type'] = $this->_currentType;
		$out['module-name'] = false;
		$out['tool-name'] = \TooBasic\classname($baseName);
		switch($this->_currentType) {
			case self::TypeCron:
				$out['tool-name'].= GC_CLASS_SUFFIX_CRON;
				$out['tool-parent'] = 'ShellCron';
				$out['tool-path'] = TB_Sanitizer::DirPath("{$parentDir}/{$Paths[GC_PATHS_SHELL_CRONS]}/{$baseName}.php");
				break;
			case self::TypeSys:
				$out['tool-name'].= GC_CLASS_SUFFIX_SYSTOOL;
				$out['tool-parent'] = 'ShellTool';
				$out['tool-path'] = TB_Sanitizer::DirPath("{$parentDir}/{$Paths[GC_PATHS_SHELL_SYSTOOLS]}/{$baseName}.php");
				break;
			case self::TypeTool:
				$out['tool-name'].= GC_CLASS_SUFFIX_TOOL;
				$out['tool-parent'] = 'ShellTool';
				$out['tool-path'] = TB_Sanitizer::DirPath("{$parentDir}/{$Paths[GC_PATHS_SHELL_TOOLS]}/{$baseName}.php");
				break;
		}

		return $out;
	}
	protected function guessType() {
		$opt = $this->_options->option(self::OptionType);
		if($opt->activated()) {
			$type = $opt->value();
			switch($type) {
				case self::TypeCron:
				case self::TypeSys:
				case self::TypeTool:
					$this->_currentType = $type;
					break;
				default:
					$this->setError(self::ErrorType, "Unknown type called '{$type}'");
			}
		} else {
			$this->setError(self::ErrorType, "No type specified");
		}
	}
	protected function loadRender() {
		if(!$this->_render) {
			$this->_render = \TooBasic\Adapter::Factory('\TooBasic\ViewAdapterSmarty');

			$engine = $this->_render->engine();
			$engine->left_delimiter = '<%';
			$engine->right_delimiter = '%>';
		}
	}
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your shell tools.');

		$text = 'Allows you to create a new shell tool and deploy it in your site.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCreate, array('create', 'new', 'add'), TBS_Option::TypeValue, $text, 'controller-name'));

		$text = 'Allows you to eliminate a shell tool and its view from your site.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionRemove, array('remove', 'rm', 'delete'), TBS_Option::TypeValue, $text, 'controller-name'));

		$text = 'This option allows you to select which type of tool you want to create. ';
		$text.= 'Options are: cron or tool';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionType, array('--type', '-t'), TBS_Option::TypeValue, $text, 'tool-type'));

		$text = 'Adds a param to be use in command line.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionParam, array('--param', '-p'), TBS_Option::TypeMultiValue, $text, 'param-name'));

		$text = 'Adds a param that triggers a method.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionMasterParam, array('--master-param', '-mp'), TBS_Option::TypeMultiValue, $text, 'param-name'));

		$text = 'Generate files inside a module.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionModule, array('--module', '-m'), TBS_Option::TypeValue, $text, 'module-name'));
	}
	protected function taskCreate($spacer = '') {
		$ok = true;

		$this->guessType();

		if(!$this->hasErrors()) {
			//
			// Global dependencies.
			global $Defaults;

			$name = $this->_options->option(self::OptionCreate)->value();

			$names = $this->genNames($name);
			echo "{$spacer}Creating shell tool '{$name}' (type: {$this->_currentType}):\n";

			if($ok) {
				echo "{$spacer}\tCreating required directories:\n";
				$dirPaths = array(
					dirname($names['tool-path'])
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
				echo "{$spacer}\tCreating tool file: ";
				if(is_file($names['tool-path'])) {
					echo TBS_Color::Yellow('Ignored').' (tool already exists)';
				} else {
					$error = false;
					if($this->genToolFile($names, $error)) {
						echo TBS_Color::Green('Ok');
						echo "\n{$spacer}\t\t'{$names['tool-path']}'";
					} else {
						echo TBS_Color::Red('Failed')." ({$error})";
						$ok = false;
					}
				}
				echo "\n";
			}
		}
	}
	protected function taskInfo($spacer = '') {
		
	}
	protected function taskRemove($spacer = '') {
		$ok = true;

		$this->guessType();

		if(!$this->hasErrors()) {
			$name = $this->_options->option(self::OptionRemove)->value();

			$names = $this->genNames($name);

			echo "{$spacer}Removing tool '{$name}' (type: {$this->_currentType}):\n";

			echo "{$spacer}\tRemoving tool file: ";
			if(!is_file($names['tool-path'])) {
				echo TBS_Color::Yellow('Ignored').' (tool already removed)';
			} else {
				@unlink($names['tool-path']);
				if(!is_file($names['tool-path'])) {
					echo TBS_Color::Green('Ok');
					echo "\n{$spacer}\t\t'{$names['tool-path']}'";
				} else {
					echo TBS_Color::Red('Failed')." (unable to remove it)";
					$ok = false;
				}
			}
			echo "\n";
		}
	}
}
