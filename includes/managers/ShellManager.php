<?php

/**
 * @file ShellManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use \TooBasic\Managers\DBStructureManager;
use \TooBasic\Shell\Options;

/**
 * @class ShellManager
 * This class hold the logic to manage a shell command execution.
 */
class ShellManager extends Manager {
	//
	// Constants.
	const ErrorNoMode = 1;
	const ErrorNotValidMode = 2;
	const ErrorNoToolName = 3;
	const ErrorNoToolClass = 4;
	const ErrorUnknownTool = 5;
	const ErrorNoProfileName = 6;
	const ErrorUnknownProfile = 7;
	const ModeCron = 'cron';
	const ModeProfile = 'profile';
	const ModeTool = 'tool';
	const ModeSys = 'sys';
	//
	// Protected properties.
	/**
	 * @var mixed[] List of error found on a execution.
	 */
	protected $_errors = array();
	/**
	 * @var string This flag indicates what type of tool is been executed.
	 */
	protected $_mode = false;
	/**
	 * @var string Name of the PHP file being called in command line.
	 */
	protected $_script = false;
	/**
	 * @var string Name of the profile to execute.
	 */
	protected $_profile = false;
	/**
	 * @var string Name of the tool to execute.
	 */
	protected $_tool = false;
	/**
	 * @var \TooBasic\Shell\ShellTool Current tool's class shortcut.
	 */
	protected $_toolClass = null;
	//
	// Public methods.
	/**
	 * This method provide access to the list of error found on an execution.
	 *
	 * @return mixed[] Returns a list of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method inidcates if an error was found on an execution.
	 *
	 * @return boolean Returns TRUE when at least one error was found.
	 */
	public function hasErrors() {
		return count($this->errors()) > 0;
	}
	/**
	 * This is the main method in charge of executing a shell tool.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 */
	public function run($spacer = '') {
		//
		// Checking for start errors.
		if(!$this->hasErrors()) {
			//
			// Setting and checking basic options.
			$options = $this->starterOptions();
			$options->check();
			//
			// Obtaining basic values required to execute.
			$this->_script = $options->script;
			$this->_mode = $options->mode;
			$this->_tool = $options->tool;
			//
			// Checking what to do based on the selected mode.
			switch($this->_mode) {
				case self::ModeProfile:
					$this->_profile = $this->_tool;
					$this->_tool = false;
					$this->runProfile($spacer, $options->unknownParams());
					break;
				case self::ModeTool:
					$this->runTool($spacer);
					break;
				case self::ModeCron:
					$this->runCron($spacer);
					break;
				case self::ModeSys:
					$this->runSys($spacer);
					break;
				default:
					//
					// At this point, the selected mode is not
					// recognized.
					//
					// Setting the proper error.
					if($this->_mode) {
						$this->setError(self::ErrorNotValidMode, "Mode '{$this->_mode}' is not valid");
					} else {
						$this->setError(self::ErrorNoMode, 'No mode specified');
					}
					//
					// Showing available modes.
					echo "{$spacer}Available modes are:\n";
					echo "{$spacer}\t- ".self::ModeTool."\n";
					echo "{$spacer}\t- ".self::ModeProfile."\n";
					echo "{$spacer}\t- ".self::ModeCron."\n";
					echo "\n";
			}
		}
		//
		// Errors are always prompted.
		$this->promptErrors($spacer);
	}
	//
	// Protected methods.
	/**
	 * Class initialization.
	 */
	protected function init() {
		parent::init();
		//
		// Checking database structure @{
		//
		// Loading database structure manager.
		$dbStructureManager = DBStructureManager::Instance();
		//
		// Checking for loading errors.
		if($dbStructureManager->hasErrors()) {
			//
			// Adding found errors int this manager.
			foreach($dbStructureManager->errors() as $error) {
				$code = $error[GC_AFIELD_CODE];
				if(is_numeric($code)) {
					$code = sprintf('%03d', $code);
				}

				$this->setError("DB-{$code}", $error[GC_AFIELD_MESSAGE]);
			}
		} else {
			//
			// Checing if the structure is correct. Otherwise, an
			// upgrade is attempted.
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					throw new Exception('Database couldn\'t be upgraded');
				}
			}
		}
		// @}
	}
	/**
	 * This simple method displays found errors on command line.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 */
	protected function promptErrors($spacer) {
		foreach($this->errors() as $error) {
			echo "{$spacer}Error: [{$error[GC_AFIELD_CODE]}] {$error[GC_AFIELD_MESSAGE]}.\n";
		}
	}
	/**
	 * This method holds the logic to execute a shell cron tool.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @param type $params List of parameters to be used by the tool as
	 * command line values.
	 */
	protected function runCron($spacer, $params = null) {
		//
		// Checking if there was a tool name given in command line.
		if($this->_tool) {
			$this->_toolClass = false;
			//
			// Generating a proper class name.
			$className = \TooBasic\Names::ShellCronClass($this->_tool);
			//
			// Checking for previous definition.
			if(class_exists($className)) {
				//
				// Creating an instance of the tool's class.
				$this->_toolClass = new $className();
			} else {
				//
				// Generating a proper path.
				$path = $this->paths->shellCron($this->_tool);
				//
				// Checking if a specification file was found.
				if($path) {
					//
					// Loading specifications.
					require_once $path;
					//
					// Checking if the specification was
					// successfully loaded.
					if(class_exists($className)) {
						//
						// Creating an instance of the tool's class.
						$this->_toolClass = new $className();
					} else {
						$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
					}
				} else {
					$this->setError(self::ErrorUnknownTool, "Unkown cron tool called '{$this->_tool}'");
				}
			}
			//
			// Checking if a tool instance was generated successfully.
			if($this->_toolClass) {
				//
				// Executing tool.
				$this->_toolClass->run($spacer, $params);
				//
				// Retrieving tool's errors.
				$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
			}
		} else {
			//
			// Setting the proper error.
			$this->setError(self::ErrorNoToolName, 'No tool name specified');
			//
			// Showing all available tools as a hint.
			echo "{$spacer}Available crons:\n";
			foreach($this->paths->shellCron('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	/**
	 * This method holds the logic to take a profile name and execute each
	 * shell cron tool associated with it.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @param string[] $extraParams List of extra parameters to append when
	 * executing each shell cron tool.
	 */
	protected function runProfile($spacer, $extraParams) {
		//
		// Global dependencies.
		global $CronProfiles;
		//
		// Checking if there was a profile name given in command line.
		if($this->_profile) {
			//
			// Checking that there's a specification for the file
			// profile name.
			if(isset($CronProfiles[$this->_profile])) {
				echo "{$spacer}Running profile '{$this->_profile}':\n";
				//
				// Running each tool configured for the profile.
				foreach($CronProfiles[$this->_profile] as $tool) {
					//
					// Setting current tool's name.
					$this->_tool = $tool[GC_CRONPROFILES_TOOL];
					//
					// Generating a list of required
					// parameters.
					$mainParams = array(
						$this->_script,
						self::ModeCron,
						$this->_tool
					);
					//
					// Forwarding execution to a single tool,
					// giving as parameters:
					//	- Required parameters:
					//		- Script name.
					//		- Mode.
					//		- Tool name.
					//	- Parameters configured on the
					//	  profile for this tool.
					//	- Extra parameters given to this
					//	  profile execution.
					$this->runCron("{$spacer}\t", array_merge($mainParams, $tool[GC_CRONPROFILES_PARAMS], $extraParams));
				}
			} else {
				$this->setError(self::ErrorUnknownProfile, "Unkown profile called '{$this->_profile}'");
			}
		} else {
			//
			// Setting the proper error.
			$this->setError(self::ErrorNoProfileName, 'No profile name specified');
			//
			// Showing all available profiles as a hint.
			echo "{$spacer}Available profiles:\n";
			if(count($CronProfiles)) {
				foreach($CronProfiles as $name => $tools) {
					echo "{$spacer}\t- '{$name}' runs:\n";
					foreach($tools as $config) {
						echo "{$spacer}\t\t- {$config[GC_CRONPROFILES_TOOL]}";
						if(count($config[GC_CRONPROFILES_PARAMS])) {
							echo ' ('.implode(' ', $config[GC_CRONPROFILES_PARAMS]).')';
						}
						echo "\n";
					}
				}
			} else {
				echo "{$spacer}\tNo profile available\n";
			}

			echo "\n";
		}
	}
	/**
	 * This method holds the logic to execute a shell system tool.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @param type $params List of parameters to be used by the tool as
	 * command line values.
	 */
	protected function runSys($spacer, $params = null) {
		//
		// Checking if there was a tool name given in command line.
		if($this->_tool) {
			$this->_toolClass = false;
			//
			// Generating a proper class name.
			$className = \TooBasic\Names::ShellSystoolClass($this->_tool);
			//
			// Checking for previous definition.
			if(class_exists($className)) {
				//
				// Creating an instance of the tool's class.
				$this->_toolClass = new $className();
			} else {
				//
				// Generating a proper path.
				$path = $this->paths->shellSys($this->_tool);
				//
				// Checking if a specification file was found.
				if($path) {
					//
					// Loading specifications.
					require_once $path;
					//
					// Checking if the specification was
					// successfully loaded.
					if(class_exists($className)) {
						//
						// Creating an instance of the tool's class.
						$this->_toolClass = new $className();
					} else {
						$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
					}
				} else {
					$this->setError(self::ErrorUnknownTool, "Unkown tool called '{$this->_tool}'");
				}
			}
			//
			// Checking if a tool instance was generated successfully.
			if($this->_toolClass) {
				//
				// Executing tool.
				$this->_toolClass->run($spacer, $params);
				//
				// Retrieving tool's errors.
				$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
			}
		} else {
			//
			// Setting the proper error.
			$this->setError(self::ErrorNoToolName, 'No tool name specified');
			//
			// Showing all available tools as a hint.
			echo "{$spacer}Available tools:\n";
			foreach($this->paths->shellSys('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	/**
	 * This method holds the logic to execute a shell tool.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @param type $params List of parameters to be used by the tool as
	 * command line values.
	 */
	protected function runTool($spacer, $params = null) {
		//
		// Checking if there was a tool name given in command line.
		if($this->_tool) {
			$this->_toolClass = false;
			//
			// Generating a proper class name.
			$className = \TooBasic\Names::ShellToolClass($this->_tool);
			//
			// Checking for previous definition.
			if(class_exists($className)) {
				//
				// Creating an instance of the tool's class.
				$this->_toolClass = new $className();
			} else {
				//
				// Generating a proper path.
				$path = $this->paths->shellTool($this->_tool);
				//
				// Checking if a specification file was found.
				if($path) {
					//
					// Loading specifications.
					require_once $path;
					//
					// Checking if the specification was
					// successfully loaded.
					if(class_exists($className)) {
						//
						// Creating an instance of the tool's class.
						$this->_toolClass = new $className();
					} else {
						$this->setError(self::ErrorNoToolClass, "Class '{$className}' doesn't exist");
					}
				} else {
					$this->setError(self::ErrorUnknownTool, "Unkown tool called '{$this->_tool}'");
				}
			}
			//
			// Checking if a tool instance was generated successfully.
			if($this->_toolClass) {
				//
				// Executing tool.
				$this->_toolClass->run($spacer, $params);
				//
				// Retrieving tool's errors.
				$this->_errors = array_merge($this->_errors, $this->_toolClass->errors());
			}
		} else {
			//
			// Setting the proper error.
			$this->setError(self::ErrorNoToolName, 'No tool name specified');
			//
			// Showing all available tools as a hint.
			echo "{$spacer}Available tools:\n";
			foreach($this->paths->shellTool('*', true) as $path) {
				$pathinfo = pathinfo($path);
				echo "{$spacer}\t- {$pathinfo['filename']}\n";
			}
			echo "\n";
		}
	}
	/**
	 * This method adds an error in the interal list.
	 *
	 * @param int $code Code to be associated with this error.
	 * @param string $message Text to be associated with this error.
	 */
	protected function setError($code, $message) {
		//
		// Formating all numerica error codes.
		if(is_numeric($code)) {
			$code = sprintf('%03d', $code);
		}
		//
		// Obtaining the error's backtrace for further information.
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		//
		// Creating shortcuts for caller and calling position from inside
		// the backtrace.
		$callingLine = array_shift($trace);
		$callerLine = array_shift($trace);
		//
		// Generating a error entry.
		$error = array(
			GC_AFIELD_CODE => "SM-{$code}",
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_CLASS => isset($callerLine['class']) ? $callerLine['class'] : false,
			GC_AFIELD_METHOD => $callerLine['function'],
			GC_AFIELD_FILE => $callingLine['file'],
			GC_AFIELD_LINE => $callingLine['line']
		);
		//
		// Adding error to and internal list.
		$this->_errors[] = $error;
	}
	/**
	 * This method sets main options that are always required when executing a
	 * shell tool.
	 *
	 * @return \TooBasic\Shell\Options Retruns an initialized options manager.
	 */
	protected function starterOptions() {
		//
		// Getting an options manager.
		$options = Options::Instance();
		//
		// Adding a main option for the script being executed.
		$options->addMainOption('script');
		//
		// Adding a main option for the type of tool to execute.
		$options->addMainOption('mode');
		//
		// Adding a main option for the name of the tool to execute.
		$options->addMainOption('tool');

		return $options;
	}
}
