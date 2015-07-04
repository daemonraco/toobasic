<?php

use TooBasic\Shell\Option as TBS_Option;
use TooBasic\Shell\Color as TBS_Color;

class CheckTool extends TooBasic\Shell\ShellTool {
	//
	// Constants.
	const OptionCheckDefaults = "CheckDefaults";
	const OptionCheckDirectories = "CheckDirectories";
	const OptionCheckPaths = "CheckPaths";
	//
	// Protected methods.
	protected function checkList($name, $list, $itemNames, $spacer = "") {
		echo "{$spacer}Global '{$name}': ";
		if(is_array($list)) {
			echo TBS_Color::Green("Ok")." (correct type)\n";
		} else {
			echo TBS_Color::Red("Failed")." (wrong type)\n";
		}
		foreach($itemNames as $key) {
			echo "{$spacer}\t'{$name}[{$key}]': ";
			if(isset($list[$key])) {
				@$str = TBS_Color::Yellow((string) $list[$key]);
				echo TBS_Color::Green("Ok")." ({$str})\n";
			} else {
				echo TBS_Color::Red("Failed\n");
			}
		}
	}
	protected function mainTask($spacer = "") {
		echo "{$spacer}Performing full check:\n";
		$this->taskCheckDefaults("{$spacer}\t");
		$this->taskCheckDirectories("{$spacer}\t");
		$this->taskCheckPaths("{$spacer}\t");
	}
	protected function setOptions() {
		$this->_options->setHelpText("This tool checks for possible configuration errors.");

		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCheckDefaults, array("-d", "--defaults"), TBS_Option::TypeNoValue));
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCheckDirectories, array("-i", "--directories"), TBS_Option::TypeNoValue));
		$this->_options->addOption(TBS_Option::EasyFactory(self::OptionCheckPaths, array("-p", "--paths"), TBS_Option::TypeNoValue));
	}
	protected function taskCheckDefaults($spacer = "") {
		echo "{$spacer}Checking default settings:\n";

		global $Defaults;

		$this->checkList("\$Defaults", $Defaults, array(
			GC_DEFAULTS_ACTION,
			GC_DEFAULTS_CACHE_ADAPTER,
			GC_DEFAULTS_CACHE_PERMISSIONS,
			GC_DEFAULTS_INSTALLED,
			GC_DEFAULTS_LANGS_DEFAULTLANG,
			GC_DEFAULTS_LAYOUT,
			GC_DEFAULTS_LANGS_BUILT,
			GC_DEFAULTS_SERVICE,
			GC_DEFAULTS_VIEW_ADAPTER,
			GC_DEFAULTS_FORMATS,
			GC_DEFAULTS_MODES,
			GC_DEFAULTS_MEMCACHED,
			GC_DEFAULTS_MEMCACHE
			), "{$spacer}\t"
		);
	}
	protected function taskCheckDirectories($spacer = "") {
		echo "{$spacer}Checking directories settings:\n";

		global $Directories;

		$this->checkList("\$Directories", $Directories, array(
			GC_DIRECTORIES_CACHE,
			GC_DIRECTORIES_CONFIGS,
			GC_DIRECTORIES_INCLUDES,
			GC_DIRECTORIES_LIBRARIES,
			GC_DIRECTORIES_REPRESENTATIONS,
			GC_DIRECTORIES_MANAGERS,
			GC_DIRECTORIES_ADAPTERS,
			GC_DIRECTORIES_ADAPTERS_CACHE,
			GC_DIRECTORIES_ADAPTERS_DB,
			GC_DIRECTORIES_ADAPTERS_VIEW,
			GC_DIRECTORIES_MODULES,
			GC_DIRECTORIES_SHELL,
			GC_DIRECTORIES_SHELL_INCLUDES,
			GC_DIRECTORIES_SHELL_FLAGS,
			GC_DIRECTORIES_SITE
			), "{$spacer}\t"
		);
	}
	protected function taskCheckPaths($spacer = "") {
		echo "{$spacer}Checking paths settings:\n";

		global $Paths;

		$this->checkList("\$Paths", $Paths, array(
			GC_PATHS_CONFIGS,
			GC_PATHS_CONTROLLERS,
			GC_PATHS_SERVICES,
			GC_PATHS_CSS,
			GC_PATHS_DBSPECS,
			GC_PATHS_IMAGES,
			GC_PATHS_JS,
			GC_PATHS_LANGS,
			GC_PATHS_LAYOUTS,
			GC_PATHS_MODELS,
			GC_PATHS_REPRESENTATIONS,
			GC_PATHS_SHELL,
			GC_PATHS_SHELL_CRONS,
			GC_PATHS_SHELL_TOOLS,
			GC_PATHS_SNIPPETS,
			GC_PATHS_TEMPLATES
			), "{$spacer}\t"
		);
	}
	protected function taskInfo($spacer = "") {
		
	}
}
