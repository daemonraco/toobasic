<?php

use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Sanitizer as TB_Sanitizer;

class ShellSystool extends TooBasic\Shell\Scaffold {
	//
	// Constants.
	const ErrorOk = 0;
	const ErrorType = 1;
	const ErrorParameters = 2;
	const OptionMasterParam = 'MasterParam';
	const OptionParam = 'Param';
	const OptionType = 'Type';
	const TypeCron = 'cron';
	const TypeSys = 'sys';
	const TypeTool = 'tool';
	//
	// Protected properties.
	protected $_currentType = false;
	protected $_scaffoldName = 'shell';
	protected $_version = TOOBASIC_VERSION;
	//
	// Protected methods.
	protected function genAssignments() {
		if($this->_assignments === false) {
			parent::genAssignments();
			//
			// Assignments.
			$this->_assignments['tool'] = $this->_names['tool-name'];
			$this->_assignments['toolParent'] = $this->_names['tool-parent'];
			$this->_assignments['options'] = array();
			$this->_assignments['masterOptions'] = array();

			$mpOpt = $this->_options->option(self::OptionMasterParam);
			$mpNames = $mpOpt->activated() ? $mpOpt->value() : array();
			$pOpt = $this->_options->option(self::OptionParam);
			$pNames = array_merge($mpNames, $pOpt->activated() ? $pOpt->value() : array());
			$triggers = array();
			$fullOption = array();
			//
			// Cleaning master option names.
			foreach($mpNames as &$mpName) {
				$pieces = explode(':', $mpName);
				$mpName = $pieces[0];
			}

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

				$this->_assignments['options'][] = $aux;
				if(in_array($name, $mpNames)) {
					$this->_assignments['masterOptions'][] = $aux;
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

			$this->_names['type'] = $this->_currentType;

			$this->_names['tool-name'] = \TooBasic\classname($this->_names['name']);
			switch($this->_currentType) {
				case self::TypeCron:
					$this->_names['tool-name'].= GC_CLASS_SUFFIX_CRON;
					$this->_names['tool-parent'] = 'ShellCron';
					$this->_names['tool-path'] = TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_SHELL_CRONS]}/{$this->_names['name']}.php");
					break;
				case self::TypeSys:
					$this->_names['tool-name'].= GC_CLASS_SUFFIX_SYSTOOL;
					$this->_names['tool-parent'] = 'ShellTool';
					$this->_names['tool-path'] = TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_SHELL_SYSTOOLS]}/{$this->_names['name']}.php");
					break;
				case self::TypeTool:
					$this->_names['tool-name'].= GC_CLASS_SUFFIX_TOOL;
					$this->_names['tool-parent'] = 'ShellTool';
					$this->_names['tool-path'] = TB_Sanitizer::DirPath("{$this->_names['parent-directory']}/{$Paths[GC_PATHS_SHELL_TOOLS]}/{$this->_names['name']}.php");
					break;
			}
			$this->_files[] = array(
				'path' => $this->_names['tool-path'],
				'template' => 'tool.html',
				'description' => 'tool file'
			);
		}
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
	protected function setOptions() {
		$this->_options->setHelpText('This tool allows you to manage your shell tools.');

		parent::setOptions();

		$text = 'Allows you to create a new shell tool and deploy it in your site.';
		$this->_options->option(self::OptionCreate)->setHelpText($text, 'tool-name');

		$text = 'Allows you to eliminate a shell tool from your site.';
		$this->_options->option(self::OptionRemove)->setHelpText($text, 'tool-name');

		$text = 'This option allows you to select which type of tool you want to create. ';
		$text.= 'Options are: cron or tool';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionType, array('--type', '-t'), TBS_Option::TypeValue, $text, 'tool-type'));

		$text = 'Adds a param to be use in command line. ';
		$text.= 'You can specify a type by writing something like \'--param name:V\'. ';
		$text.= "Possiblilities values are \n";
		$text.= "\t- 'N' for simple parameters (default).\n";
		$text.= "\t- 'V' for options with one value.\n";
		$text.= "\t- 'M' for options with multiple values.\n";
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionParam, array('--param', '-p'), TBS_Option::TypeMultiValue, $text, 'param-name'));

		$text = 'Adds a param that triggers a method.';
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionMasterParam, array('--master-param', '-mp'), TBS_Option::TypeMultiValue, $text, 'param-name'));
	}
	protected function taskCreate($spacer = '') {
		$ok = true;

		$this->guessType();

		if(!$this->hasErrors()) {
			$this->genNames();

			echo "{$spacer}Creating shell tool '{$this->_names['name']}' (type: {$this->_currentType}):\n";

			$ok = parent::taskCreate($spacer);
		}

		return $ok;
	}
	protected function taskInfo($spacer = '') {
		
	}
	protected function taskRemove($spacer = '') {
		$ok = true;

		$this->guessType();

		if(!$this->hasErrors()) {
			$this->genNames();

			echo "{$spacer}Removing tool '{$this->_names['name']}' (type: {$this->_currentType}):\n";

			$ok = parent::taskRemove($spacer);
		}

		return $ok;
	}
}
