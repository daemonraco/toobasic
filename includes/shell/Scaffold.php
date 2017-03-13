<?php

/**
 * @file Scaffold.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Shell;

//
// Class aliases.
use TooBasic\Shell\Color;
use TooBasic\Shell\Option;
use TooBasic\Sanitizer;

/**
 * @class Scaffold
 * @abstract
 * This is class represents a basic logic for a system shell tool capable of
 * generating TooBasic scaffolds.
 */
abstract class Scaffold extends ShellTool {
	//
	// Constants.
	const OPTION_CREATE = 'Create';
	const OPTION_FORCED = 'Forced';
	const OPTION_MODULE = 'Module';
	const OPTION_REMOVE = 'Remove';
	//
	// Protected properties.
	/**
	 * @var mixed[string] List values to be use inside a scaffold tempalte.
	 */
	protected $_assignments = false;
	/**
	 * @var string[string][] Lists of configuration lines to add into PHP
	 * files.
	 */
	protected $_configLines = false;
	/**
	 * @var string[] List of files to be created.
	 */
	protected $_files = [];
	/**
	 * @var boolean This flag indicates if existing files have to be
	 * overwritten.
	 */
	protected $_forced = null;
	/**
	 * @var mixed[string] Internal list of values to be used on scaffold
	 * generations.
	 */
	protected $_names = false;
	/**
	 * @var \TooBasic\Adapters\View\Smarty Shortcut to a Smarty view adapter.
	 */
	protected $_render = false;
	protected $_requiredDirectories = [];
	/**
	 * @var \stdClass[] List of routes to be added as configuration.
	 */
	protected $_routes = false;
	/**
	 * @var \stdClass[] List of table-routes to be added as configuration.
	 */
	protected $_tableRoutes = false;
	/**
	 *
	 * @var string General name of the scaffold to be generated, it's used to
	 * the proper set of templates.
	 */
	protected $_scaffoldName = '';
	/**
	 * @var string[string] Smarty delimiters to use on generated scaffolds.
	 */
	protected $_smartyDelimiters = [
		GC_AFIELD_LEFT => false,
		GC_AFIELD_RIGHT => false
	];
	/**
	 * @var \stdClass[] List of language translations to be added as
	 * configuration for the current language.
	 */
	protected $_translations = false;
	//
	// Protected methods.
	/**
	 * This method injects confiugration lines into PHP files.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function addConfigLines($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Checking for errors and the existens of at least one line.
		if($ok && count($this->_configLines)) {
			echo "\n{$spacer}Injecting configuration lines:\n";
			//
			// Creating pending files.
			foreach($this->_configLines as $path => $confLines) {
				if(!is_file($path)) {
					echo "{$spacer}\tCreating empty file '{$path}': ";
					file_put_contents($path, "<?php\n");
					echo Color::Green("Done\n");
				}
			}
			//
			// Modifying each file.
			foreach($this->_configLines as $path => $confLines) {
				//
				// Default values.
				$sections = [
					GC_AFIELD_START => [],
					GC_AFIELD_MIDDLE => [],
					GC_AFIELD_END => []
				];
				//
				// Adding required end of line;
				foreach($confLines as $pos => $confLine) {
					$confLines[$pos] = "{$confLine}\n";
				}
				//
				// Flag lines.
				$startLine = '// TOOBASIC-SYSTOOL-'.strtoupper($this->_scaffoldName)."[START]\n";
				$endLine = '// TOOBASIC-SYSTOOL-'.strtoupper($this->_scaffoldName)."[END]\n";
				//
				// Streching out file lines.
				$startFound = false;
				$endFound = false;
				foreach(file($path) as $line) {
					//
					// Fixing end of line.
					$line = str_replace("\r\n", "\n", $line);
					//
					// Separating sections.
					if(!$startFound) {
						if($line == $startLine) {
							$startFound = true;
						} else {
							$sections[GC_AFIELD_START][] = $line;
						}
					} else {
						if(!$endFound) {
							if($line == $endLine) {
								$endFound = true;
							} else {
								$sections[GC_AFIELD_MIDDLE][] = $line;
							}
						} else {
							$sections[GC_AFIELD_END][] = $line;
						}
					}
				}
				//
				// If this is the first time this section is
				// added, a new line should be added to keep it
				// nice.
				if(!$startFound) {
					$sections[GC_AFIELD_START][] = "\n";
				}
				//
				// Removing conf lines for order safety.
				foreach($sections[GC_AFIELD_MIDDLE] as $pos => $line) {
					if(in_array($line, $confLines)) {
						unset($sections[GC_AFIELD_MIDDLE][$pos]);
					}
				}
				//
				// Appending new conf lines.
				foreach($confLines as $confLine) {
					$sections[GC_AFIELD_MIDDLE][] = $confLine;
				}
				//
				// Re-building file
				$builtLines = [];
				foreach($sections[GC_AFIELD_START] as $line) {
					$builtLines[] = $line;
				}
				$builtLines[] = $startLine;
				foreach($sections[GC_AFIELD_MIDDLE] as $line) {
					$builtLines[] = $line;
				}
				$builtLines[] = $endLine;
				foreach($sections[GC_AFIELD_END] as $line) {
					$builtLines[] = $line;
				}
				//
				// Updating.
				echo "{$spacer}\tUpdating '{$path}': ";
				if(file_put_contents($path, implode('', $builtLines)) !== false) {
					echo Color::Green("Done\n");
				} else {
					echo Color::Green("Failed\n");
				}
			}
		}

		return $ok;
	}
	/**
	 * This method is the one in charge of triggering the generation of routes
	 * and adding them into configuration.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function addAllRoutes($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Adding routes if everything is ok.
		if($ok) {
			//
			// Generating the list of required routes.
			$this->genRoutes();
			//
			// Checking if there's at least one route to be added.
			if(count($this->_routes)) {
				echo "{$spacer}Adding routes configuration:\n";
				//
				// Checking each route.
				foreach($this->_routes as $route) {
					echo "{$spacer}\t- '{$route->route}': ";
					//
					// Attempting to add a route.
					$error = '';
					$fatal = false;
					if($this->addRoute($route, $error, $fatal)) {
						echo Color::Green('Ok');
					} else {
						//
						// Checking the severity of the
						// error found.
						if($fatal) {
							echo Color::Red('Failed');
							$ok = false;
							break;
						} else {
							echo Color::Yellow('Ignored');
						}
						echo " ({$error})";
					}
					echo "\n";
				}
			}
			//
			// Checking if there's at least one table-route to be
			// added.
			if(count($this->_tableRoutes)) {
				echo "{$spacer}Adding table-routes configuration:\n";
				//
				// Checking each route.
				foreach($this->_tableRoutes as $table) {
					echo "{$spacer}\t- '{$table->singularName}': ";
					//
					// Attempting to add a table-route.
					$error = '';
					$fatal = false;
					if($this->addTableRoute($table, $error, $fatal)) {
						echo Color::Green('Ok');
					} else {
						//
						// Checking the severity of the
						// error found.
						if($fatal) {
							echo Color::Red('Failed');
							$ok = false;
							break;
						} else {
							echo Color::Yellow('Ignored');
						}
						echo " ({$error})";
					}
					echo "\n";
				}
			}
		}

		return $ok;
	}
	/**
	 * This method is the one in charge of triggering the generation of
	 * language translations and adding them into configuration.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function addAllTranslations($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Global dependencies.
		global $LanguageName;
		//
		// Generating translations.
		$this->genTranslations();
		//
		// Checking if there are translations to add.
		if($ok && count($this->_translations)) {
			echo "{$spacer}Adding translation ({$LanguageName}):\n";
			//
			// Cechking each translation item.
			foreach($this->_translations as $tr) {
				echo "{$spacer}\t- '{$tr->key}': ";
				//
				// Attempting to add a translation.
				$error = '';
				$fatal = false;
				if($this->addTranslation($tr, $error, $fatal)) {
					echo Color::Green('Ok');
				} else {
					//
					// Checking the severity of the error
					// found.
					if($fatal) {
						echo Color::Red('Failed');
						$ok = false;
						break;
					} else {
						echo Color::Yellow('Ignored');
					}
					echo " ({$error})";
				}
				echo "\n";
			}
		}

		return $ok;
	}
	/**
	 * This method adds a single route into configuration.
	 *
	 * @param \stdClass $newRoute Route to be added.
	 * @param string $error Error message given when something goes wrong.
	 * @param boolean $fatal This flag indicats if it's a fatal error or not.
	 * @return boolean Returns TRUE there were no errors.
	 */
	protected function addRoute(\stdClass $newRoute, &$error, &$fatal) {
		//
		// Defualt values.
		$ok = !$this->hasErrors();
		$backup = false;
		$fatal = false;
		$error = '';
		$config = false;
		//
		// Checking if there's a route configuration file prensent in the
		// system.
		if($ok && !is_file($this->_names[GC_AFIELD_ROUTES_PATH])) {
			//
			// Attemptting to create a routes confifuration file with
			// a basic JSON configuraion.
			if(!file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], '{"routes":[]}')) {
				$ok = false;
				$error = "unable to create file '{$this->_names[GC_AFIELD_ROUTES_PATH]}'";
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Backing up current configuration to avoid problems in
			// further steps.
			$backup = file_get_contents($this->_names[GC_AFIELD_ROUTES_PATH]);
			//
			// Loading current configuration.
			$config = json_decode($backup);
			//
			// If the configuration was not loaded it's considered to
			// be a fatal error.
			if(!$config) {
				$ok = false;
				$error = 'unable to use routes file';
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Enforcing structure.
			if(!isset($config->routes)) {
				$config->routes = [];
			}
			//
			// Checking each route to avoid duplicates. If there's
			// already a similar route it should not be replaced.
			foreach($config->routes as $route) {
				if(isset($newRoute->action) && isset($route->action) && $route->action == $newRoute->action) {
					if($route->route == $newRoute->route) {
						$ok = false;
						$error = "there's another rule for this controller";
						break;
					}
				} elseif(isset($newRoute->service) && isset($route->service) && $route->service == $newRoute->service) {
					if($route->route == $newRoute->route) {
						$ok = false;
						$error = "there's another rule for this service";
						break;
					}
				}
			}
		}
		if($ok) {
			//
			// Appending the new route.
			$config->routes[] = $newRoute;
			//
			// Saving the configuration and checking it's successfully
			// saved.
			if(!file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], json_encode($config, JSON_PRETTY_PRINT))) {
				$ok = false;
				$error = 'something went wrong writing back routes file';
				$fatal = true;
				//
				// Restoring back up.
				file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], $backup);
			}
		}
		//
		// Retruning the final status of the whole operation.
		return $ok;
	}
	/**
	 * This method adds a single table-route into configuration.
	 *
	 * @param \stdClass $newRoute Route to be added.
	 * @param string $error Error message given when something goes wrong.
	 * @param boolean $fatal This flag indicats if it's a fatal error or not.
	 * @return boolean Returns TRUE there were no errors.
	 */
	protected function addTableRoute(\stdClass $newRoute, &$error, &$fatal) {
		//
		// Defualt values.
		$ok = !$this->hasErrors();
		$backup = false;
		$fatal = false;
		$error = '';
		$config = false;
		//
		// Checking if there's a route configuration file prensent in the
		// system.
		if($ok && !is_file($this->_names[GC_AFIELD_ROUTES_PATH])) {
			//
			// Attemptting to create a routes confifuration file with
			// a basic JSON configuraion.
			if(!file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], '{"tables":{}}')) {
				$ok = false;
				$error = "unable to create file '{$this->_names[GC_AFIELD_ROUTES_PATH]}'";
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Backing up current configuration to avoid problems in
			// further steps.
			$backup = file_get_contents($this->_names[GC_AFIELD_ROUTES_PATH]);
			//
			// Loading current configuration.
			$config = json_decode($backup);
			//
			// If the configuration was not loaded it's considered to
			// be a fatal error.
			if(!$config) {
				$ok = false;
				$error = 'unable to use routes file';
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Enforcing structure.
			if(!isset($config->tables)) {
				$config->tables = new \stdClass();
			}
			//
			// Checking table-routes to avoid duplicates. If there's
			// already a similar table-route it should not be
			// replaced.
			if(isset($config->tables->{$newRoute->singularName})) {
				$ok = false;
				$error = "there's another rule for this table";
			}
		}
		if($ok) {
			//
			// Object to append.
			$aux = new \stdClass();
			if(isset($newRoute->pluralName)) {
				$aux->plural = $newRoute->pluralName;
			}
			$aux->predictive = isset($newRoute->predictive) ? $newRoute->predictive : false;
			$aux->searchable = isset($newRoute->searchable) ? $newRoute->searchable : false;
			//
			// Appending the new table-route.
			$config->tables->{$newRoute->singularName} = $aux;
			//
			// Saving the configuration and checking it's successfully
			// saved.
			if(!file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], json_encode($config, JSON_PRETTY_PRINT))) {
				$ok = false;
				$error = 'something went wrong writing back routes file';
				$fatal = true;
				//
				// Restoring back up.
				file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], $backup);
			}
		}
		//
		// Retruning the final status of the whole operation.
		return $ok;
	}
	/**
	 * This method adds a single language translation into configuration.
	 *
	 * @param \stdClass $newTr Route to be added.
	 * @param string $error Error message given when something goes wrong.
	 * @param boolean $fatal This flag indicats if it's a fatal error or not.
	 * @return boolean Returns TRUE there were no errors.
	 */
	protected function addTranslation($newTr, &$error, &$fatal) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		$backup = false;
		$fatal = false;
		$error = '';
		$config = false;
		//
		// Checking if there's a language configuration file present in
		// the system.
		if($ok && !is_file($this->_names[GC_AFIELD_LANGS_PATH])) {
			//
			// Creating a basic language configuration file with an
			// initial JSON configuration.
			if(!file_put_contents($this->_names[GC_AFIELD_LANGS_PATH], '{"keys":[]}')) {
				$ok = false;
				$error = "unable to create file '{$this->_names[GC_AFIELD_LANGS_PATH]}'";
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Backing up current configuration to avoid problems in
			// further steps.
			$backup = file_get_contents($this->_names[GC_AFIELD_LANGS_PATH]);
			//
			// Loading current configuration.
			$config = json_decode($backup);
			//
			// If the configuration was not loaded it's considered to
			// be a fatal error.
			if(!$config) {
				$ok = false;
				$error = 'unable to use language file';
				$fatal = true;
			}
		}
		if($ok) {
			//
			// Checking each translation key to avoid duplicates. If
			// there's already a similar key it should not be
			// replaced.
			foreach($config->keys as $tr) {
				if($tr->key == $newTr->key) {
					$ok = false;
					$error = 'key already present';
					break;
				}
			}
		}
		if($ok) {
			//
			// Appending the new translation key.
			$config->keys[] = $newTr;
			//
			// Saving the configuration and checking it's successfully
			// saved.
			if(!file_put_contents($this->_names[GC_AFIELD_LANGS_PATH], json_encode($config, JSON_PRETTY_PRINT))) {
				$ok = false;
				$error = 'something went wrong writing back language file';
				$fatal = true;
				//
				// Restoring back up.
				file_put_contents($this->_names[GC_AFIELD_LANGS_PATH], $backup);
			}
		}

		//
		// Retruning the final status of the whole operation.
		return $ok;
	}
	/**
	 * This method looking into each generated file path and adds its
	 * directory path to a list for further creation. Also it sets some
	 * required details (when not present) on each file description.
	 */
	protected function enforceFilesList() {
		//
		// Cecking each file.
		foreach($this->_files as &$file) {
			//
			// Enforcing the presence of a description.
			if(!isset($file[GC_AFIELD_DESCRIPTION])) {
				$file[GC_AFIELD_DESCRIPTION] = "file '".basename($file[GC_AFIELD_PATH])."'";
			}
			//
			// Setting the scaffold builder method as
			// 'genFileByTemplate()' when there's none.
			if(!isset($file[GC_AFIELD_GENERATOR])) {
				$file[GC_AFIELD_GENERATOR] = 'genFileByTemplate';
			}
			//
			// Enforcing template configuration.
			if(!isset($file[GC_AFIELD_TEMPLATE])) {
				$file[GC_AFIELD_TEMPLATE] = false;
			}
			//
			// Adding directory path as required.
			$this->_requiredDirectories[] = dirname($file[GC_AFIELD_PATH]);
		}
	}
	/**
	 * This method generates the list of values to be use inside a scaffold
	 * tempalte. In this class it's a just an initializer, it should be
	 * implemented by inherited classes.
	 */
	protected function genAssignments() {
		//
		// Triggering names generation.
		$this->genNames();
		//
		// Avoiding multiple generations.
		if(!$this->hasErrors() && $this->_assignments === false) {
			//
			// Default values.
			$this->_assignments = [];
		}
	}
	/**
	 * This method generates lists of configuration lines to be inserted in
	 * several PHP files.
	 */
	protected function genConfigLines() {
		//
		// Triggering names generation.
		$this->genNames();
		//
		// Avoiding multiple generations.
		if(!$this->hasErrors() && $this->_configLines === false) {
			//
			// Default values.
			$this->_configLines = [];
		}
	}
	/**
	 * This method is the one in charge of generating a specific file based on
	 * a template file and a rendering callback method.
	 * Actualy, it's the initial point on a generation, the real generation is
	 * called from here.
	 *
	 * @param string $path Absolute path where the results must be stored.
	 * @param string $template Name of the template on which results will be
	 * based.
	 * @param string $callback Method in charge of the actual file generation.
	 * @return boolean Returns TRUE There were no errors.
	 */
	protected function genFile($path, $template = false, $callback = 'genFileByTemplate') {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Ingnoring process when there are previous errors.
		if($ok) {
			//
			// Avoiding file overwrite.
			if(!$this->isForced() && is_file($path)) {
				echo Color::Yellow('Ignored').' (file already exist)';
			} else {
				//
				// Generating a more complete scaffold template
				// name.
				$completeTemplate = Sanitizer::DirPath("scaffolds/{$this->_scaffoldName}/{$template}");
				//
				// Forwarding the generation call and checking for
				// errors.
				$error = false;
				if($this->{$callback}($path, $completeTemplate, $error)) {
					echo Color::Green('Ok');
				} else {
					echo Color::Red('Failed')." ({$error})";
					$ok = false;
				}
			}
			echo "\n";
		}

		return $ok;
	}
	/**
	 * This method holdes the logic to generate a file based on a scaffold
	 * template.
	 *
	 * @param string $path Absolute path where the file must be created.
	 * @param string $template Nam eof the template to use as base.
	 * @param string $error Error message.
	 * @return boolean Return TRUE when there where no errors.
	 */
	protected function genFileByTemplate($path, $template, &$error) {
		//
		// Default values.
		$out = !$this->hasErrors();
		//
		// Ingnoring process when there are previous errors.
		if($out) {
			//
			// Forcing render to be loaded.
			$this->loadRender();
			//
			// Assignments.
			$this->genAssignments();
			//
			// Generating file content and replacing delimiters for
			// the proper value.
			$output = str_replace([
				'%STYLEFT%',
				'%STYRIGHT%'
				], [
				$this->_smartyDelimiters[GC_AFIELD_LEFT],
				$this->_smartyDelimiters[GC_AFIELD_RIGHT]
				], $this->_render->render($this->_assignments, $template));
			//
			// Generating a file.
			$result = file_put_contents($path, $output);
			//
			// Checking for errors.
			if($result === false) {
				$error = "Unable to write file '{$path}'";
				$out = false;
			}
		}

		return $out;
	}
	/**
	 * This method is called when the list of internal values has to be
	 * generated.
	 */
	protected function genNames() {
		//
		// Avoiding multiple analysis.
		if(!$this->hasErrors() && $this->_names === false) {
			//
			// Global dependecies.
			global $LanguageName;
			global $Directories;
			global $Paths;
			//
			// Default values.
			$this->_names = [];
			//
			// Base name.
			$baseName = '';
			$cOpt = $this->_options->option(self::OPTION_CREATE);
			$rOpt = $this->_options->option(self::OPTION_REMOVE);
			if($cOpt->activated()) {
				$baseName = $cOpt->value();
			} elseif($rOpt->activated()) {
				$baseName = $rOpt->value();
			}
			//
			// Basic internal values.
			$this->_names[GC_AFIELD_NAME] = $baseName;
			$this->_names[GC_AFIELD_MODULE_NAME] = false;
			$this->_names[GC_AFIELD_PARENT_DIRECTORY] = false;
			//
			// Checking module and parent directory.
			$opt = $this->_options->option(self::OPTION_MODULE);
			if($opt->activated()) {
				$this->_names[GC_AFIELD_MODULE_NAME] = $opt->value();
				$this->_names[GC_AFIELD_PARENT_DIRECTORY] = "{$Directories[GC_DIRECTORIES_MODULES]}/{$this->_names[GC_AFIELD_MODULE_NAME]}";
			} else {
				$this->_names[GC_AFIELD_PARENT_DIRECTORY] = $Directories[GC_DIRECTORIES_SITE];
			}
			//
			// Routes configuration file path.
			$this->_names[GC_AFIELD_ROUTES_PATH] = Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_CONFIGS]}/routes.json");
			$this->_requiredDirectories[] = dirname($this->_names[GC_AFIELD_ROUTES_PATH]);
			//
			// Language translation configuration file path for
			// current language.
			$this->_names[GC_AFIELD_LANGS_PATH] = Sanitizer::DirPath("{$this->_names[GC_AFIELD_PARENT_DIRECTORY]}/{$Paths[GC_PATHS_LANGS]}/{$LanguageName}.json");
			$this->_requiredDirectories[] = dirname($this->_names[GC_AFIELD_LANGS_PATH]);
		}
	}
	/**
	 * This method is the one in charge of creating all requiered directories
	 * before creating any file
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function genRequiredDirectories($spacer) {
		$ok = !$this->hasErrors();
		//
		// Ingnoring process when there are previous errors.
		if($ok) {
			//
			// Cleaning directories list.
			$this->_requiredDirectories = array_unique($this->_requiredDirectories);
			//
			// Checking which directories have to be created.
			$toGen = [];
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
					//
					// Checking creation.
					if(is_dir($dirPath)) {
						echo Color::Green('Ok')."\n";
					} else {
						echo Color::Red('Failed')."\n";
						$ok = false;
						break;
					}
				}
			}
		}

		return $ok;
	}
	/**
	 * This method generates the list of routes to be added as configuration.
	 * In this class it's a just an initializer, it should be implemented by
	 * inherited classes.
	 */
	protected function genRoutes() {
		//
		// Avoiding multiple generations.
		if(!$this->hasErrors()) {
			if($this->_routes === false) {
				$this->_routes = [];
			}
			if($this->_tableRoutes === false) {
				$this->_tableRoutes = [];
			}
		}
	}
	/**
	 * This method generates the list of language translations to be added as
	 * configuration. In this class it's a just an initializer, it should be
	 * implemented by inherited classes.
	 */
	protected function genTranslations() {
		//
		// Triggering names generation.
		$this->genNames();
		//
		// Avoiding multiple generations.
		if(!$this->hasErrors() && $this->_translations === false) {
			//
			// Default values.
			$this->_translations = [];
		}
	}
	/**
	 * This method allows to know if this is an forced execution where files
	 * are overwritten.
	 *
	 * @return boolean Returns TRUE when it's a forced execution.
	 */
	protected function isForced() {
		//
		// Checking given options if it hasn't been done yet.
		if($this->_forced === null) {
			$this->_forced = $this->_options->option(self::OPTION_FORCED)->activated();
		}

		return $this->_forced;
	}
	/**
	 * Loading and setting a shortcut to a view renderer based on Samrty.
	 */
	protected function loadRender() {
		//
		// Checking that it hasn't been loaded before.
		if(!$this->_render) {
			//
			// Global dependencies.
			global $Defaults;
			//
			// Creating a proper view adapter.
			$this->_render = \TooBasic\Adapters\Adapter::Factory('\\TooBasic\\Adapters\\View\\Smarty');
			//
			// Configurating the Smarty object with some specific
			// parameters required for schaffolds.
			$engine = $this->_render->engine();
			$engine->left_delimiter = '<%';
			$engine->right_delimiter = '%>';
			$engine->force_compile = true;
			//
			// Loading delimiter replacements.
			if($Defaults[GC_SMARTY_LEFT_DELIMITER] !== false && $Defaults[GC_SMARTY_RIGHT_DELIMITER] !== false) {
				$this->_smartyDelimiters[GC_AFIELD_LEFT] = $Defaults[GC_SMARTY_LEFT_DELIMITER];
				$this->_smartyDelimiters[GC_AFIELD_RIGHT] = $Defaults[GC_SMARTY_RIGHT_DELIMITER];
			} else {
				$this->_smartyDelimiters[GC_AFIELD_LEFT] = '{';
				$this->_smartyDelimiters[GC_AFIELD_RIGHT] = '}';
			}
		}
	}
	/**
	 * This method is the one in charge of triggering the generation of routes
	 * and removing them from configuration.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function removeAllRoutes($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Removing routes only if everything is ok.
		if($ok) {
			//
			// Generating the list of required routes.
			$this->genRoutes();
			//
			// Checking if there's at least one route to be removed.
			if(count($this->_routes)) {
				echo "{$spacer}Removing routes configuration:\n";
				//
				// Checking each route.
				foreach($this->_routes as $route) {
					echo "{$spacer}\t- '{$route->route}': ";
					//
					// Attempting to remove a route.
					$error = '';
					$fatal = false;
					if($this->removeRoute($route, $error, $fatal)) {
						echo Color::Green('Ok');
					} else {
						//
						// Checking the severity of the
						// error found.
						if($fatal) {
							echo Color::Red('Failed');
							$ok = false;
							break;
						} else {
							echo Color::Yellow('Ignored');
						}
						echo " ({$error})";
					}
					echo "\n";
				}
			}
		}

		return $ok;
	}
	/**
	 * This method is the one in charge of triggering the generation of
	 * language translations and removing them from configuration.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function removeAllTranslations($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Ingnoring process when there are previous errors.
		if($ok) {
			//
			// Global dependencies.
			global $LanguageName;
			//
			// Generating translations.
			$this->genTranslations();
			//
			// Checking if there are translations to remove.
			if(count($this->_translations)) {
				echo "{$spacer}Removing translations ({$LanguageName}):\n";
				//
				// Cechking each translation item.
				foreach($this->_translations as $tr) {
					echo "{$spacer}\t- '{$tr->key}': ";
					//
					// Attempting to remove a translation.
					$error = '';
					$fatal = false;
					if($this->removeTranslation($tr, $error, $fatal)) {
						echo Color::Green('Ok');
					} else {
						//
						// Checking the severity of the
						// error found.
						if($fatal) {
							echo Color::Red('Failed');
							$ok = false;
							break;
						} else {
							echo Color::Yellow('Ignored');
						}
						echo " ({$error})";
					}
					echo "\n";
				}
			}
		}

		return $ok;
	}
	/**
	 * This method removes injected confiugration lines into PHP files.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function removeConfigLines($spacer) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Checking for errors and the existens of at least one line.
		if($ok && count($this->_configLines)) {
			echo "\n{$spacer}Removing configuration lines:\n";
			//
			// Modifying each file.
			foreach($this->_configLines as $path => $confLines) {
				//
				// Ignoring unexisting files.
				if(!is_file($path)) {
					continue;
				}
				//
				// Default values.
				$sections = [
					GC_AFIELD_START => [],
					GC_AFIELD_MIDDLE => [],
					GC_AFIELD_END => []
				];
				//
				// Adding required end of line;
				foreach($confLines as $pos => $confLine) {
					$confLines[$pos] = "{$confLine}\n";
				}
				//
				// Flag lines.
				$startLine = '// TOOBASIC-SYSTOOL-'.strtoupper($this->_scaffoldName)."[START]\n";
				$endLine = '// TOOBASIC-SYSTOOL-'.strtoupper($this->_scaffoldName)."[END]\n";
				//
				// Streching out file lines.
				$startFound = false;
				$endFound = false;
				foreach(file($path) as $line) {
					//
					// Fixing end of line.
					$line = str_replace("\r\n", "\n", $line);
					//
					// Separating sections.
					if(!$startFound) {
						if($line == $startLine) {
							$startFound = true;
						} else {
							$sections[GC_AFIELD_START][] = $line;
						}
					} else {
						if(!$endFound) {
							if($line == $endLine) {
								$endFound = true;
							} else {
								$sections[GC_AFIELD_MIDDLE][] = $line;
							}
						} else {
							$sections[GC_AFIELD_END][] = $line;
						}
					}
				}
				//
				// This file doesn't seems to have configuration
				// generated by this controller. Ignoring.
				if(!$startFound) {
					continue;
				}
				//
				// Removing conf lines for order safety.
				foreach($sections[GC_AFIELD_MIDDLE] as $pos => $line) {
					if(in_array($line, $confLines)) {
						unset($sections[GC_AFIELD_MIDDLE][$pos]);
					}
				}
				//
				// Re-building file
				$builtLines = [];
				foreach($sections[GC_AFIELD_START] as $line) {
					$builtLines[] = $line;
				}
				$builtLines[] = $startLine;
				foreach($sections[GC_AFIELD_MIDDLE] as $line) {
					$builtLines[] = $line;
				}
				$builtLines[] = $endLine;
				foreach($sections[GC_AFIELD_END] as $line) {
					$builtLines[] = $line;
				}
				//
				// Updating.
				echo "{$spacer}\tUpdating '{$path}': ";
				if(file_put_contents($path, implode('', $builtLines)) !== false) {
					echo Color::Green("Done\n");
				} else {
					echo Color::Green("Failed\n");
				}
			}
		}

		return $ok;
	}
	/**
	 * This method is the one in charge of removing a generated file and
	 * checking the results of such operation.
	 *
	 * @param string $path Absolute path of the file to be remove.
	 * @return boolean Returns TRUE when it's successfully removed.
	 */
	protected function removeFile($path) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Ingnoring process when there are previous errors.
		if($ok) {
			//
			// Checking the existence of the file.
			if(!is_file($path)) {
				echo Color::Yellow('Ignored').' (file already removed)';
			} else {
				//
				// Remove it.
				@unlink($path);
				//
				// Checking for errors.
				if(!is_file($path)) {
					echo Color::Green('Ok');
				} else {
					echo Color::Red('Failed').' (unable to remove it)';
					$ok = false;
				}
			}
			echo "\n";
		}

		return $ok;
	}
	/**
	 * This method removes a single route from configuration.
	 *
	 * @param \stdClass $badRoute Route to be removed.
	 * @param string $error Error message given when something goes wrong.
	 * @param boolean $fatal This flag indicats if it's a fatal error or not.
	 * @return boolean Returns TRUE there were no errors.
	 */
	protected function removeRoute(\stdClass $badRoute, &$error, &$fatal) {
		//
		// Defualt values.
		$ok = !$this->hasErrors();
		$backup = false;
		$fatal = false;
		$error = '';
		$config = false;
		//
		// Checking if there's a route configuration file prensent in the
		// system.
		if($ok && !is_file($this->_names[GC_AFIELD_ROUTES_PATH])) {
			$ok = false;
			$error = "unable to find file '{$this->_names[GC_AFIELD_ROUTES_PATH]}'";
			$fatal = true;
		}
		if($ok) {
			//
			// Backing up current configuration to avoid problems in
			// further steps.
			$backup = file_get_contents($this->_names[GC_AFIELD_ROUTES_PATH]);
			//
			// Loading current configuration.
			$config = json_decode($backup);
			//
			// If the configuration was not loaded it's considered to
			// be a fatal error.
			if(!$config) {
				$ok = false;
				$error = 'unable to use routes file';
				$fatal = true;
			}
		}
		if($ok) {
			$found = false;
			//
			// Looking for the route and removing it.
			foreach($config->routes as $routeKey => $route) {
				if(isset($badRoute->action) && isset($route->action) && $route->action == $badRoute->action) {
					unset($config->routes[$routeKey]);
					$found = true;
				} elseif(isset($badRoute->service) && isset($route->service) && $route->service == $badRoute->service) {
					unset($config->routes[$routeKey]);
					$found = true;
				}
			}
			//
			// Checking if there's something save back into
			// configuration or not.
			if($found) {
				//
				// Cleaning rotues list keys to avoid worng JSON
				// encoding.
				$config->routes = array_values($config->routes);
				//
				// Enconding and saving routes.
				if(!file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], json_encode($config, JSON_PRETTY_PRINT))) {
					$ok = false;
					$error = 'something went wrong writing back routes file';
					$fatal = true;
					//
					// Restoring back up.
					file_put_contents($this->_names[GC_AFIELD_ROUTES_PATH], $backup);
				}
			} else {
				$ok = false;
				$error = 'no routes found';
			}
		}
		//
		// Retruning the final status of the whole operation.
		return $ok;
	}
	/**
	 * This method removes a single language translation from configuration.
	 *
	 * @param \stdClass $badTr Route to be added.
	 * @param string $error Error message given when something goes wrong.
	 * @param boolean $fatal This flag indicats if it's a fatal error or not.
	 * @return boolean Returns TRUE there were no errors.
	 */
	protected function removeTranslation(\stdClass $badTr, &$error, &$fatal) {
		//
		// Default values.
		$ok = !$this->hasErrors();
		$backup = false;
		$fatal = false;
		$error = '';
		$config = false;
		//
		// Checking if there's a language configuration file present in
		// the system.
		if($ok && !is_file($this->_names[GC_AFIELD_LANGS_PATH])) {
			$ok = false;
			$error = "unable to find file '{$this->_names[GC_AFIELD_LANGS_PATH]}'";
			$fatal = true;
		}
		if($ok) {
			//
			// Backing up current configuration to avoid problems in
			// further steps.
			$backup = file_get_contents($this->_names[GC_AFIELD_LANGS_PATH]);
			//
			// Loading current configuration.
			$config = json_decode($backup);
			//
			// If the configuration was not loaded it's considered to
			// be a fatal error.
			if(!$config) {
				$ok = false;
				$error = 'unable to use language file';
				$fatal = true;
			}
		}
		if($ok) {
			$found = false;
			//
			// Looking for the translation key and removing it.
			foreach($config->keys as $trPos => $tr) {
				if($tr->key == $badTr->key) {
					unset($config->keys[$trPos]);
					$found = true;
				}
			}
			//
			// Checking if there's something save back into
			// configuration or not.
			if($found) {
				//
				// Cleaning translations list keys to avoid worng
				// JSON encoding.
				$config->keys = array_values($config->keys);
				//
				// Enconding and saving routes.
				if(!file_put_contents($this->_names[GC_AFIELD_LANGS_PATH], json_encode($config, JSON_PRETTY_PRINT))) {
					$ok = false;
					$error = 'something went wrong writing back laguage file';
					$fatal = true;
					//
					// Restoring back up.
					file_put_contents($this->_names[GC_AFIELD_LANGS_PATH], $backup);
				}
			} else {
				$ok = false;
				$error = 'no translations found';
			}
		}
		//
		// Retruning the final status of the whole operation.
		return $ok;
	}
	/**
	 * This method sets some default options for this scaffold class. It's
	 * suggested that inherited classes update their help texts.
	 */
	protected function setOptions() {
		$text = 'Use: $this->_options->option(self::OPTION_CREATE)->setHelpText(\'text\', \'valueName\');';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_CREATE, ['create', 'new', 'add'], Option::TYPE_VALUE, $text, 'name'));

		$text = 'Use: $this->_options->option(self::OPTION_REMOVE)->setHelpText(\'text\', \'valueName\');';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_REMOVE, ['remove', 'rm', 'delete'], Option::TYPE_VALUE, $text, 'name'));

		$text = 'Generate files inside a module.';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_MODULE, ['--module', '-m'], Option::TYPE_VALUE, $text, 'name'));

		$text = 'Overwrite files when they exist (routes are excluded).';
		$this->_options->addOption(Option::EasyFactory(self::OPTION_FORCED, ['--forced'], Option::TYPE_NO_VALUE, $text));
	}
	/**
	 * This is the main task in charge of creating scaffold assets.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function taskCreate($spacer = '') {
		//
		// Enforcing names generation.
		$this->genNames();
		//
		// Enforcing assignment generation.
		$this->genAssignments();
		//
		// Enforcing files list structure.
		$this->genConfigLines();
		//
		// Enforcing files list structure.
		$this->enforceFilesList();
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Directories
		if($ok) {
			$ok = $this->genRequiredDirectories($spacer);
			//
			// In the event a new module folder had been created,
			// 'Paths' requires a reset.
			$this->paths->reset();
		}
		//
		// Files
		if($ok) {
			foreach($this->_files as $file) {
				echo "{$spacer}\tCreating {$file[GC_AFIELD_DESCRIPTION]}: ";
				if(!$this->genFile($file[GC_AFIELD_PATH], $file[GC_AFIELD_TEMPLATE], $file[GC_AFIELD_GENERATOR])) {
					$ok = false;
				}
				echo "{$spacer}\t\t- '{$file[GC_AFIELD_PATH]}'\n\n";

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
		//
		// Translations.
		if($ok) {
			$ok = $this->addAllTranslations("{$spacer}\t");
		}
		//
		// PHP  configuration lines.
		if($ok) {
			$ok = $this->addConfigLines("{$spacer}\t");
		}

		return $ok;
	}
	/**
	 * This is the main task in charge of removing scaffold assets.
	 *
	 * @param string $spacer Prefix to add on each log line promptted on
	 * terminal.
	 * @return boolean Returns TRUE where there were no critical errors.
	 */
	protected function taskRemove($spacer = '') {
		//
		// Enforcing names generation.
		$this->genNames();
		//
		// Enforcing assignment generation.
		$this->genAssignments();
		//
		// Enforcing files list structure.
		$this->genConfigLines();
		//
		// Enforcing files list structure.
		$this->enforceFilesList();
		//
		// Default values.
		$ok = !$this->hasErrors();
		//
		// Files
		if($ok) {
			foreach($this->_files as $file) {
				echo "{$spacer}\tRemoving {$file[GC_AFIELD_DESCRIPTION]}: ";
				if(!$this->removeFile($file[GC_AFIELD_PATH])) {
					$ok = false;
				}
				echo "{$spacer}\t\t- '{$file[GC_AFIELD_PATH]}'\n\n";

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
		//
		// PHP  configuration lines.
		if($ok) {
			$ok = $this->removeConfigLines("{$spacer}\t");
		}

		return $ok;
	}
}
