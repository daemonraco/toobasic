<?php

/**
 * @file ActionsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ActionsManager
 */
class ActionsManager extends UrlManager {
	//
	// Protected methods.
	protected $_currentRedirector = false;
	//
	// Public methods.
	/**
	 * 
	 * @global string $ActionName
	 * @param boolean $autoDisplay
	 * @return mixed 
	 */
	public function run($autoDisplay = true) {
		//
		// Global dependencies.
		global $ActionName;
		//
		// Default values.
		$layoutName = false;
		//
		// Current action execution.
		$actionLastRun = self::ExecuteAction($ActionName, null, $layoutName);
		//
		// Checking if there's a redirector set.
		if(isset($actionLastRun[GC_AFIELD_REDIRECTOR])) {
			$this->executeRedirector($actionLastRun[GC_AFIELD_REDIRECTOR]);
		} else {
			//
			// Layout execution (if any).
			$layoutLastRun = false;
			//
			// Running layout's controller.
			if($layoutName) {
				$layoutLastRun = self::ExecuteAction($layoutName, $actionLastRun);
			}
			//
			// Displaying if required.
			if($autoDisplay) {
				$headers = array();
				if($layoutLastRun) {
					$headers = array_merge($headers, $layoutLastRun[GC_AFIELD_HEADERS]);
				}
				$headers = array_merge($headers, $actionLastRun[GC_AFIELD_HEADERS]);

				foreach($headers as $name => $value) {
					header("{$name}: {$value}");
				}
				//
				// If there's a layout present, controller's result must
				// be shown inside a layout's result.
				if($layoutLastRun) {
					echo str_replace('%TOO_BASIC_ACTION_CONTENT%', $actionLastRun[GC_AFIELD_RENDER], $layoutLastRun[GC_AFIELD_RENDER]);
				} else {
					echo $actionLastRun[GC_AFIELD_RENDER];
				}
			}
		}

		return $actionLastRun;
	}
	//
	// Protected methods.
	/**
	 * This method stops the process of an action and performs a redirection
	 * to another url.
	 *
	 * @param string $redirector Redirector configuration name.
	 */
	protected function executeRedirector($redirector) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking redirection configuration.
		if(isset($Defaults[GC_DEFAULTS_REDIRECTIONS][$redirector])) {
			//
			// Configuration shortcut.
			$redirConf = $Defaults[GC_DEFAULTS_REDIRECTIONS][$redirector];
			//
			// If current redirector is an alias, it must be
			// forwarded.
			if(isset($redirConf[GC_AFIELD_ALIAS])) {
				$this->executeRedirector($redirConf[GC_AFIELD_ALIAS]);
			} else {
				//
				// Checking configuration for parameter 'action'.
				if(!isset($redirConf[GC_AFIELD_ACTION])) {
					throw new Exception("Wrong redirection configuration '{$redirector}'. No configuration found for parameter '".GC_AFIELD_ACTION."'");
				}
				//
				// Checking configuration for parameter 'params'.
				if(!isset($redirConf[GC_AFIELD_PARAMS])) {
					$redirConf[GC_AFIELD_PARAMS] = array();
				} elseif(!is_array($redirConf[GC_AFIELD_PARAMS])) {
					throw new Exception("Wrong redirection configuration '{$redirector}'. Parameter '".GC_AFIELD_PARAMS."' is not an array");
				}
				//
				// Checking configuration for parameter 'layout'.
				if(!isset($redirConf[GC_AFIELD_LAYOUT])) {
					$redirConf[GC_AFIELD_LAYOUT] = false;
				}
				//
				// Basic url
				$url = ROOTURI."/?action={$redirConf[GC_AFIELD_ACTION]}";
				//
				// Adding the layout parameter.
				if($redirConf[GC_AFIELD_LAYOUT]) {
					$url.= "&".GC_REQUEST_LAYOUT."={$redirConf[GC_AFIELD_LAYOUT]}";
				}
				//
				// Adding params.
				foreach($redirConf[GC_AFIELD_PARAMS] as $key => $value) {
					if(is_numeric($key)) {
						$url.= "&{$value}";
					} elseif($value === null) {
						$url.= "&{$key}";
					} else {
						$url.= "&{$key}={$value}";
					}
				}
				//
				// Adding the old url.
				$url.= '&'.GC_REQUEST_REDIRECTOR.'='.urlencode($this->params->server->REQUEST_URI);
				//
				// Cleaning and replacing routes.
				$url = \TooBasic\RoutesManager::Instance()->enroute($url);
				//
				// Is it a debug or the real deal?
				if(isset($this->params->debugredirection)) {
					\TooBasic\debugThing(function() use ($redirector, $redirConf, $url) {
						$spacer = "    ";
						echo "Redirect condition reached:\n";
						echo "{$spacer}- url:           '{$url}'\n";
						echo "{$spacer}- redirector:    '{$redirector}'\n";

						echo "{$spacer}- configuration:\n";
						echo "{$spacer}{$spacer}- action: '{$redirConf[GC_AFIELD_ACTION]}'\n";
						echo "{$spacer}{$spacer}- parameters:\n";
						ksort($redirConf[GC_AFIELD_PARAMS]);
						foreach($redirConf[GC_AFIELD_PARAMS] as $key => $value) {
							if(is_numeric($key)) {
								echo "{$spacer}{$spacer}{$spacer}- '{$value}'\n";
							} else {
								echo "{$spacer}{$spacer}{$spacer}- '{$key}' [value: '{$value}']\n";
							}
						}
					});
				} else {
					header("Location: {$url}");
				}
				//
				// A redirect contition always stops here.
				die;
			}
		} else {
			throw new Exception("Redirection code '{$redirector}' is not configured");
		}
	}
	/**
	 * Manager initialization.
	 */
	protected function init() {
		parent::init();

		$dbStructureManager = DBStructureManager::Instance();
		if($dbStructureManager->hasErrors()) {
			foreach($dbStructureManager->errors() as $error) {
				$code = $error[GC_AFIELD_CODE];
				if(is_numeric($code)) {
					$code = sprintf('%03d', $code);
				}

				throw new Exception("[DB-{$code}] {$error[GC_AFIELD_MESSAGE]}");
			}
			throw new Exception('There are database errors specs');
		} else {
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					throw new Exception('Database couldn\'t be upgraded');
				}
			}
		}
	}
	//
	// Public class methods.
	public static function ExecuteAction($actionName, $previousActionRun = null, &$layoutName = false) {
		//
		// Default values.
		$redirector = false;
		$status = true;
		//
		// Loading controller based on current action name.
		$controllerClass = self::FetchController($actionName);
		if($controllerClass !== false) {
			$layoutName = $controllerClass->layout();
			//
			// If there's a previous run, this must be the layout and
			// the previous run comes from the controller, so
			// assignments from that controller should be available
			// for the layout for different reason like setting the
			// page title.
			if(is_array($previousActionRun)) {
				/** @fixme There should be a way to change layout's cache key setting from the controller. */
				$controllerClass->massiveAssign($previousActionRun[GC_AFIELD_ASSIGNMENTS]);
			}

			$redirector = $controllerClass->checkRedirectors();
			if(!$redirector) {
				$status = $controllerClass->run();
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}

		$lastRun = false;
		if($status) {
			$lastRun = $controllerClass->lastRun();
		} elseif($redirector) {
			$lastRun = array(
				GC_AFIELD_REDIRECTOR => $redirector
			);
		} else {
			$errorActionName = HTTPERROR_NOT_FOUND;
			if($controllerClass instanceof Exporter) {
				$lastError = $controllerClass->lastError();
				if($lastError) {
					$errorActionName = $lastError[GC_AFIELD_CODE];
				} else {
					$errorActionName = HTTPERROR_INTERNAL_SERVER_ERROR;
				}
			}

			$errorControllerClass = self::FetchController($errorActionName);
			if($controllerClass !== false) {
				$errorControllerClass->setFailingController($controllerClass);
			} else {
				$whatIsIt = (is_array($previousActionRun) ? 'action layout' : 'action');
				$errorControllerClass->setErrorMessage("Unable to find {$whatIsIt} '{$actionName}'");
			}
			$layoutName = $errorControllerClass->layout();
			$errorControllerClass->run();
			$lastRun = $errorControllerClass->lastRun();
		}

		return $lastRun;
	}
	/**
	 * This class method looks for a controller based on an action name.
	 *
	 * @param string $actionName Action name from which guess a controller's
	 * name.
	 * @return \TooBasic\Controller Returns a controllers object or false on
	 * failure.
	 */
	public static function FetchController($actionName, $recursive = false) {
		//
		// Default values.
		$out = false;
		//
		// Looking for a controller with the given action name as a file
		// name.
		$controllerPath = Paths::Instance()->controllerPath($actionName);
		//
		// Checking the obtained path.
		if($controllerPath) {
			//
			// Loading physical file with the controllers definition.
			require_once $controllerPath;
			//
			// Guessing the right class name.
			$controllerClassName = (is_numeric($actionName) ? 'N' : '').\TooBasic\classname($actionName).GC_CLASS_SUFFIX_CONTROLLER;
			//
			// Creating the controllers class.
			if(class_exists($controllerClassName)) {
				$out = new $controllerClassName($actionName);
			} else {
				throw new Exception("Class '{$controllerClassName}' is not defined. File '{$controllerPath}' doesn't seem to load the right object.");
			}
		} elseif(!$recursive) {
			//
			// If there's no controller file, but there's a template,
			// a virtual controller is used.
			//
			// Global dependecies.
			global $ModeName;
			//
			// Searching for a template.
			$template = Paths::Instance()->templatePath($actionName, $ModeName);
			//
			// Checking if there's a template for the given name.
			if($template) {
				//
				// Loading a virtual controller.
				$out = self::FetchController('too_basic_virtual', true);
				//
				// Setting view name to the virtual controller.
				if($out) {
					$out->setViewName($actionName);
				}
			}
		}
		//
		// Returning fetched controller.
		return $out;
	}
}
