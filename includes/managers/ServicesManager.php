<?php

/**
 * @file ServicesManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\MagicProp;
use TooBasic\Names;
use TooBasic\Params;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class ServicesManager
 * This manager is the one in charge of interpreting a service request and
 * executing it.
 */
class ServicesManager extends UrlManager {
	//
	// Magic methods.
	const ERROR_OK = 0;
	const ERROR_UNKNOWN = 1;
	const ERROR_JSON_ENCODE = 2;
	const ERROR_UNKNOWN_SERVICE = 3;
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
		global $ServiceName;
		//
		// Checking if the service has to be executed or just explain its
		// interface.
		if(isset($this->params->get->explaininterface)) {
			//
			// Checking if there's a specific service to be explained
			// or if the explanation was requested for all services.
			if(isset($this->params->get->service)) {
				$serviceLastRun = $this->explainInterface($ServiceName);
			} else {
				$serviceLastRun = $this->explainInterfaces();
			}
		} else {
			//
			// Current service execution.
			$serviceLastRun = self::ExecuteService($ServiceName);
		}
		//
		// Displaying if required.
		if($autoDisplay) {
			//
			// Every service response is a JSON object.
			header('Content-Type: application/json');
			//
			// Setting headers.
			foreach($serviceLastRun[GC_AFIELD_HEADERS] as $name => $value) {
				header("{$name}: {$value}");
			}
			unset($serviceLastRun[GC_AFIELD_HEADERS]);
			//
			// Encoding response as JSON.
			$out = json_encode($serviceLastRun);
			//
			// Checking for any JSON encoding error.
			if(json_last_error() != JSON_ERROR_NONE) {
				//
				// Geneating a error response.
				$out = json_encode([
					GC_AFIELD_ERROR => [
						GC_AFIELD_CODE => self::ERROR_JSON_ENCODE,
						GC_AFIELD_MESSAGE => 'Unable to encode output'
					],
					GC_AFIELD_DATA => null
				]);
			}
			//
			// Displaying the output.
			echo $out;
		}
		//
		// Returning last run results.
		return $serviceLastRun;
	}
	//
	// Protected methods.
	/**
	 * This method generates the output for a specific service interface
	 * explanation.
	 *
	 * @param string $serviceName Name of the service to explain.
	 * @return mixed[string] Returns the explanation generated.
	 * @throws \TooBasic\Exception
	 */
	protected function explainInterface($serviceName) {
		//
		// Default values.
		$out = false;
		//
		// Attempting to obtaing a previous explanation generated and
		// stored on cache @{
		$cachePrefix = 'SERVICEINTERFACE';
		$cacheKey = $serviceName;
		$cache = MagicProp::Instance()->cache;
		if(!isset(Params::Instance()->debugresetcache)) {
			$out = $cache->get($cachePrefix, $cacheKey);
		}
		// @}
		//
		// If there where no cached explanation it's generated.
		if(!$out) {
			//
			// Basic fields.
			$out = [
				GC_AFIELD_STATUS => true,
				GC_AFIELD_INTERFACE => null,
				GC_AFIELD_HEADERS => [],
				GC_AFIELD_ERROR => false,
				GC_AFIELD_ERRORS => []
			];
			//
			// Basic fields on a interface.
			$out[GC_AFIELD_INTERFACE] = [
				GC_AFIELD_NAME => $serviceName,
				GC_AFIELD_CACHED => false,
				GC_AFIELD_METHODS => []
			];
			//
			// Looking for the service file.
			$serviceFile = $this->paths->servicePath($serviceName);
			/** @todo this should use 'self::FetchService()' */
			$service = false;
			//
			// Checking service existence
			if($serviceFile && is_readable($serviceFile)) {
				$pathinfo = pathinfo($serviceFile);
				//
				// Generating information about the file where it
				// is stored.
				$service = [
					GC_AFIELD_PATH => $serviceFile,
					GC_AFIELD_NAME => $pathinfo['filename'],
					GC_AFIELD_CLASS => Names::ServiceClass($pathinfo['filename'])
				];
			} else {
				//
				// GEnerating an error description for the fact
				// that the service was not found.
				$error = [
					GC_AFIELD_CODE => self::ERROR_UNKNOWN_SERVICE,
					GC_AFIELD_MESSAGE => "Service '{$serviceName}' not found",
					GC_AFIELD_LOCATION => [
						GC_AFIELD_METHOD => __CLASS__.'::'.__FUNCTION__.'()',
						GC_AFIELD_FILE => __FILE__,
						GC_AFIELD_LINE => __LINE__
					]
				];
				//
				// Changing a fiew result values and adding the
				// error.
				$out[GC_AFIELD_STATUS] = false;
				$out[GC_AFIELD_INTERFACE] = false;
				$out[GC_AFIELD_ERROR] = $error;
				$out[GC_AFIELD_ERRORS][] = $error;
			}
			//
			// Checking if a service was found or not.
			if($service) {
				//
				// Loading service's file.
				require_once $service[GC_AFIELD_PATH];
				//
				// Checking class existence.
				if(!class_exists($service[GC_AFIELD_CLASS])) {
					throw new Exception(Translate::Instance()->EX_undefined_class_on_file([
						'name' => $service[GC_AFIELD_CLASS],
						'path' => $service[GC_AFIELD_PATH]
					]));
				}
				//
				// Creating the service class.
				$object = new $service[GC_AFIELD_CLASS]($service[GC_AFIELD_NAME]);
				//
				// Loading the list of requierd parameters.
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS] = $object->requiredParams();
				//
				// Is it a cached service?
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CACHED] = $object->cached();
				//
				// Loading the list of parameters used on cache
				// keys generation.
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CACHE_PARAMS] = $object->cacheParams();
				//
				// Loading specifications for CORS.
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CORS] = $object->corsSpecs();
				//
				// Loading teh list of HTTP method through which
				// it can be accessed.
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_METHODS] = $object->methods();
				//
				// Removing empty lists from each HTTP method.
				foreach($out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS] as $method => $value) {
					if(!$value) {
						unset($out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS][$method]);
					}
				}
			}
			//
			// Storing this explanation in cache to avoid issues with
			// multiple requests attacks.
			$cache->save($cachePrefix, $cacheKey, $out);
		}

		return $out;
	}
	/**
	 * This method generates a explanation for all service interfaces.
	 *
	 * @param string $serviceName Name of the service to explain.
	 * @return mixed[string] Returns the explanation generated.
	 * @throws \TooBasic\Exception
	 */
	protected function explainInterfaces() {
		//
		// Default values.
		$out = false;
		//
		// Attempting to obtaing a previous explanation generated and
		// stored on cache @{
		$cachePrefix = 'SERVICEINTERFACES';
		$cacheKey = 'FULL';
		$cache = MagicProp::Instance()->cache;
		if(!isset(Params::Instance()->debugresetcache)) {
			$out = $cache->get($cachePrefix, $cacheKey);
		}
		// @}
		//
		// If there where no cached explanation it's generated.
		if(!$out) {
			//
			// Basic fields.
			$out = [
				GC_AFIELD_STATUS => true,
				GC_AFIELD_SERVICES => [],
				GC_AFIELD_HEADERS => [],
				GC_AFIELD_ERROR => false,
				GC_AFIELD_ERRORS => []
			];
			//
			// Searching for all known services and generating a
			// explanation for each one.
			foreach($this->paths->servicePath('*', true) as $serviceFile) {
				$pathinfo = pathinfo($serviceFile);
				//
				// Generating a specific interface explanation.
				$service = $this->explainInterface($pathinfo['filename']);
				//
				// Queuing the explanation.
				$out[GC_AFIELD_SERVICES][] = $service[GC_AFIELD_INTERFACE];
			}
			//
			// Storing this explanation in cache to avoid issues with
			// multiple requests attacks.
			$cache->save($cachePrefix, $cacheKey, $out);
		}

		return $out;
	}
	//
	// Public class methods.
	/**
	 * This class method loads a specific service, execute it and retruns it's
	 * result.
	 *
	 * @param string $serviceName Name of the service to look for and execute.
	 * @return mixed[string] Returns the result of the execution.
	 */
	public static function ExecuteService($serviceName) {
		//
		// Generating a basic result with an unknown error.
		$lastRun = [
			GC_AFIELD_STATUS => false,
			GC_AFIELD_DATA => null,
			GC_AFIELD_ERROR => false,
			GC_AFIELD_ERRORS => [],
			GC_AFIELD_HEADERS => []
		];
		//
		// Loading serivce file and obtaining its class.
		$serviceClass = self::FetchService($serviceName);
		//
		// Checking if the load was a success.
		if($serviceClass !== false) {
			//
			// Actually running the service.
			$serviceClass->run();
			//
			// Getting its results.
			$lastRun = $serviceClass->lastRun();
		} else {
			//
			// Setting the right error information.
			$aux = [
				GC_AFIELD_CODE => self::ERROR_UNKNOWN_SERVICE,
				GC_AFIELD_MESSAGE => "Service '{$serviceName}' not found"
			];
			$lastRun[GC_AFIELD_ERROR] = $aux;
			$lastRun[GC_AFIELD_ERRORS][] = $aux;
			$lastRun[GC_AFIELD_HEADERS] = [];
			$lastRun[GC_AFIELD_DATA] = null;
			$lastRun[GC_AFIELD_STATUS] = false;
		}

		return $lastRun;
	}
	/**
	 * This class method searches and loads a service based on its name.
	 *
	 * @param string $serviceName Name of the service to look for.
	 * @return \TooBasic\Service Returns a services object or FALSE if it
	 * cannot be found.
	 * @throws \TooBasicException
	 */
	public static function FetchService($serviceName) {
		//
		// Default values.
		$out = false;
		//
		// Generating the right class name for the service.
		$serviceClassName = Names::ServiceClass($serviceName);
		//
		// Checking if the class hasn't been previously loaded.
		if(class_exists($serviceClassName)) {
			//
			// Creating a instance of the service.
			$out = new $serviceClassName($serviceName);
		} else {
			//
			// Looking for a file where the service should be defined.
			$servicePath = Paths::Instance()->servicePath(Names::ServiceFilename($serviceName));
			//
			// Checking if there's a way to load the specification.
			if($servicePath && is_readable($servicePath)) {
				//
				// Loading the specification.
				require_once $servicePath;
				//
				// Checking if the required class was loaded.
				if(class_exists($serviceClassName)) {
					//
					// Creating a instance of the service.
					$out = new $serviceClassName($serviceName);
				} else {
					//
					// No specification is a fatal error.
					throw new Exception(Translate::Instance()->EX_undefined_class(['name' => $serviceClassName]));
				}
			}
		}

		return $out;
	}
}
