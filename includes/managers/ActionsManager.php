<?php

/**
 * @file ActionsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Exporter;
use TooBasic\Managers\DBStructureManager;
use TooBasic\Managers\ManifestsManager;
use TooBasic\Names;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class ActionsManager
 * This manager is the one in charge of interpreting a action/controller request
 * and executing it.
 */
class ActionsManager extends UrlManager {
	//
	// Protected methods.
	/**
	 * @var string Name of the current redirection configuration (if any was
	 * triggered).
	 */
	protected $_currentRedirector = false;
	//
	// Public methods.
	/**
	 * This is the main method to call in order to execute a service.
	 *
	 * @param boolean $autoDisplay This flag tells to actually prompt the
	 * generated output.
	 * @return mixed[string] Returns the execution's result.
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
				//
				// Default values.
				$errorLayout = false;
				$errored = false;
				//
				// Loading layout.
				$result = self::ExecuteAction($layoutName, $actionLastRun, $errorLayout, $errored);
				//
				// Checking if the layout failed and was built
				// using an error controller.
				if($errored) {
					//
					// If it failed, the result is considered
					// to be an action result and it's
					// sub-layout is check.
					$actionLastRun = $result;
					//
					// Running error controller's layout.
					if($errorLayout) {
						$layoutLastRun = self::ExecuteAction($errorLayout, $actionLastRun, $errorLayout, $errored);
						//
						// At this point, the layout
						// should not fail, if it does
						// it's a fatal exception.
						if($errored) {
							throw new Exception("Layout '{$errorLayout}' for current error page failed on its execution.");
						}
					}
				} else {
					$layoutLastRun = $result;
				}
			}
			//
			// Displaying if required.
			if($autoDisplay) {
				//
				// Generating a full list of required headers.
				$headers = array();
				//
				// Adding layout headers if there was a layout
				// executed.
				if($layoutLastRun) {
					$headers = array_merge($headers, $layoutLastRun[GC_AFIELD_HEADERS]);
				}
				$headers = array_merge($headers, $actionLastRun[GC_AFIELD_HEADERS]);
				//
				// Setting headers.
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
				$url = \TooBasic\Managers\RoutesManager::Instance()->enroute($url);
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
		//
		// Checking modules status @{
		$manifestsManager = ManifestsManager::Instance();
		if(!$manifestsManager->check()) {
			//
			// Obtaining the first error found.
			$errors = $manifestsManager->errors();
			$error = array_shift($errors);
			//
			// Building a message to show.
			$exceptionMessage = "[MF-{$error[GC_AFIELD_CODE]}] {$error[GC_AFIELD_MESSAGE]}";
			if($error[GC_AFIELD_MODULE_NAME]) {
				$exceptionMessage.= " (module: {$error[GC_AFIELD_MODULE_NAME]})";
			}
			//
			// Throwing the exception.
			throw new Exception($exceptionMessage);
		}
		// @}
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

				throw new Exception($this->tr->EX_DB_error_message([
					'type' => 'DB',
					'code' => $code,
					'message' => $error[GC_AFIELD_MESSAGE]
				]));
			}
			throw new Exception($this->tr->EX_DB_has_spec_errors);
		} else {
			//
			// Checing if the structure is correct. Otherwise, an
			// upgrade is attempted.
			if(!$dbStructureManager->check()) {
				if(!$dbStructureManager->upgrade()) {
					throw new Exception($this->tr->EX_DB_unable_to_upgrade);
				}
			}
		}
		// @}
	}
	//
	// Public class methods.
	/**
	 * This class method loads a specific action/controller, execute it and
	 * retruns it's result.
	 *
	 * @param string $actionName Name of the action/controller to execute.
	 * @param mixed[string] $previousActionRun Execution results from a
	 * previous controller, useful for layouts.
	 * @param string $layoutName Returns the name of the layout used by the
	 * controller.
	 * @param boolean $errorTriggered Returns TRUE when the result was build
	 * by an error controller.
	 * @return mixed[string] Returns an execution result structure.
	 * @throws \TooBasic\Exception
	 */
	public static function ExecuteAction($actionName, $previousActionRun = null, &$layoutName = false, &$errorTriggered = false) {
		//
		// Default values.
		$redirector = false;
		$status = true;
		$lastRun = false;
		$layoutName = false;
		$errorTriggered = false;
		//
		// Loading controller based on current action's name.
		$controllerClass = self::FetchController($actionName);
		//
		// Checking if the load was a success.
		if($controllerClass !== false) {
			//
			// Obtaining a name for the layout used by the controller.
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
			//
			// Checking if a redirector was triggered.
			$redirector = $controllerClass->checkRedirectors();
			//
			// Executing the controller unless a redirector was
			// triggered.
			if(!$redirector) {
				$status = $controllerClass->run();
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}
		//
		// Checking execution status.
		if($status) {
			//
			// At this point, the controller was executed
			// successfully.
			$lastRun = $controllerClass->lastRun();
		} elseif($redirector) {
			//
			// At this point, a redirector was triggered and it should
			// be used to generate a execution result.
			$lastRun = array(
				GC_AFIELD_REDIRECTOR => $redirector
			);
		} else {
			//
			// At this point there was an error executing the
			// controller.
			//
			// Global dependencies.
			global $Defaults;
			//
			// The default error is a HTTP-404.
			$errorActionName = HTTPERROR_NOT_FOUND;
			//
			// Checking if the controller's class was at least loaded.
			if($controllerClass instanceof Exporter) {
				//
				// Obtainig the last error found by the
				// controller.
				$lastError = $controllerClass->lastError();
				//
				// If the controller found an error, its code is
				// used as error page name. Otherwise, it is
				// consider to be HTTP-500 error.
				if($lastError) {
					$errorActionName = $lastError[GC_AFIELD_CODE];
				} else {
					$errorActionName = HTTPERROR_INTERNAL_SERVER_ERROR;
				}
			}
			//
			// Checking error page handlers.
			if(!isset($Defaults[GC_DEFAULTS_ERROR_PAGES][$errorActionName])) {
				throw new Exception(Translate::Instance()->EX_unhandled_HTTP_error(['code' => $errorActionName]));
			}
			//
			// Loading a proper error page controller.
			$errorControllerClass = self::FetchController($Defaults[GC_DEFAULTS_ERROR_PAGES][$errorActionName]);
			//
			// Adding more information about the error.
			if($errorControllerClass !== false) {
				if($controllerClass) {
					$errorControllerClass->setFailingController($controllerClass);
				} else {
					$errorControllerClass->setErrorMessage("Unknown action '{$actionName}'");
				}
			} else {
				$whatIsIt = (is_array($previousActionRun) ? 'action layout' : 'action');
				throw new Exception(Translate::Instance()->EX_unable_to_find_action_with_error([
					'type' => $whatIsIt,
					'action' => $actionName,
					'error' => $errorActionName
				]));
			}
			//
			// Loading layout name used by the error page.
			$layoutName = $errorControllerClass->layout();
			//
			// Executing the error page.
			$errorControllerClass->run();
			//
			// Fetching controller's execution result.
			$lastRun = $errorControllerClass->lastRun();
			//
			// Indicating that this run was build base on an error
			// controller.
			$errorTriggered = true;
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
			$controllerClassName = Names::ControllerClass($actionName);
			//
			// Creating the controllers class.
			if(class_exists($controllerClassName)) {
				$out = new $controllerClassName($actionName);
			} else {
				throw new Exception(Translate::Instance()->EX_undefined_class_on_file([
					'name' => $controllerClassName,
					'path' => $controllerPath
				]));
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
