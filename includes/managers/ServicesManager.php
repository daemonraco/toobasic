<?php

/**
 * @file ServicesManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

use TooBasic\Paths;
use TooBasic\Params;

/**
 * @class ServicesManager
 * 
 * This manager is the one in charge of interpreting a service request and
 * executing it.
 */
class ServicesManager extends UrlManager {
	//
	// Magic methods.
	const ErrorOk = 0;
	const ErrorUnknown = 1;
	const ErrorJSONEncode = 2;
	const ErrorUnknownService = 3;
	//
	// Public methods.
	public function run($autoDisplay = true) {
		//
		// Global requirements.
		global $ServiceName;

		if(isset($this->params->get->explaininterface)) {
			if(isset($this->params->get->service)) {
				$serviceLastRun = $this->explainInterface($ServiceName);
			} else {
				$serviceLastRun = $this->explainInterfaces();
			}
		} else {
			//
			// Current action execution.
			$serviceLastRun = self::ExecuteService($ServiceName);
		}
		//
		// Displaying if required.
		if($autoDisplay) {
			header('Content-Type: application/json');

			foreach($serviceLastRun[GC_AFIELD_HEADERS] as $name => $value) {
				header("{$name}: {$value}");
			}
			unset($serviceLastRun[GC_AFIELD_HEADERS]);

			$out = json_encode($serviceLastRun);
			if(json_last_error() != JSON_ERROR_NONE) {
				$out = json_encode(array(
					GC_AFIELD_ERROR => array(
						GC_AFIELD_CODE => self::ErrorJSONEncode,
						GC_AFIELD_MESSAGE => 'Unable to encode output'
					),
					GC_AFIELD_DATA => null
				));
			}

			echo $out;
		}

		return $serviceLastRun;
	}
	//
	// Protected methods.
	protected function explainInterface($serviceName) {
		$out = false;

		$cachePrefix = 'SERVICEINTERFACE';
		$cacheKey = $serviceName;
		$cache = MagicProp::Instance()->cache;
		if(!isset(Params::Instance()->debugresetcache)) {
			$out = $cache->get($cachePrefix, $cacheKey);
		}

		if(!$out) {
			$out = array(
				GC_AFIELD_STATUS => true,
				GC_AFIELD_INTERFACE => null,
				GC_AFIELD_HEADERS => array(),
				GC_AFIELD_ERROR => false,
				GC_AFIELD_ERRORS => array()
			);

			$out[GC_AFIELD_INTERFACE] = array(
				GC_AFIELD_NAME => $serviceName,
				GC_AFIELD_CACHED => false,
				GC_AFIELD_METHODS => array()
			);

			$serviceFile = $this->paths->servicePath($serviceName);
			$service = false;
			if($serviceFile && is_readable($serviceFile)) {
				$pathinfo = pathinfo($serviceFile);

				$service = array(
					GC_AFIELD_PATH => $serviceFile,
					GC_AFIELD_NAME => $pathinfo['filename'],
					GC_AFIELD_CLASS => \TooBasic\Names::ServiceClass($pathinfo['filename'])
				);
			} else {
				$error = array(
					GC_AFIELD_CODE => self::ErrorUnknownService,
					GC_AFIELD_MESSAGE => "Service '{$serviceName}' not found",
					GC_AFIELD_LOCATION => array(
						GC_AFIELD_METHOD => __CLASS__.'::'.__FUNCTION__.'()',
						GC_AFIELD_FILE => __FILE__,
						GC_AFIELD_LINE => __LINE__
					)
				);

				$out[GC_AFIELD_STATUS] = false;
				$out[GC_AFIELD_INTERFACE] = false;
				$out[GC_AFIELD_ERROR] = $error;
				$out[GC_AFIELD_ERRORS][] = $error;
			}

			if($service) {
				require_once $service[GC_AFIELD_PATH];
				//
				// Checking class existence.
				if(!class_exists($service[GC_AFIELD_CLASS])) {
					throw new Exception("Class '{$service[GC_AFIELD_CLASS]}' is not defined. File '{$service[GC_AFIELD_PATH]}' doesn't seem to load the right object.");
				}
				//
				// Creating the service class.
				$object = new $service[GC_AFIELD_CLASS]($service[GC_AFIELD_NAME]);

				$out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS] = $object->requiredParams();
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CACHED] = $object->cached();
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CACHE_PARAMS] = $object->cacheParams();
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_CORS] = $object->corsSpecs();
				$out[GC_AFIELD_INTERFACE][GC_AFIELD_METHODS] = $object->methods();

				foreach($out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS] as $method => $value) {
					if(!$value) {
						unset($out[GC_AFIELD_INTERFACE][GC_AFIELD_REQUIRED_PARAMS][$method]);
					}
				}
			}

			$cache->save($cachePrefix, $cacheKey, $out);
		}

		return $out;
	}
	protected function explainInterfaces() {
		$out = false;

		$cachePrefix = 'SERVICEINTERFACES';
		$cacheKey = 'FULL';
		$cache = MagicProp::Instance()->cache;
		if(!isset(Params::Instance()->debugresetcache)) {
			$out = $cache->get($cachePrefix, $cacheKey);
		}

		if(!$out) {
			$out = array(
				GC_AFIELD_STATUS => true,
				GC_AFIELD_SERVICES => array(),
				GC_AFIELD_HEADERS => array(),
				GC_AFIELD_ERROR => false,
				GC_AFIELD_ERRORS => array()
			);

			foreach($this->paths->servicePath('*', true) as $serviceFile) {
				$pathinfo = pathinfo($serviceFile);

				$service = $this->explainInterface($pathinfo['filename']);
				$out[GC_AFIELD_SERVICES][] = $service[GC_AFIELD_INTERFACE];
			}

			$cache->save($cachePrefix, $cacheKey, $out);
		}

		return $out;
	}
	//
	// Public class methods.
	public static function ExecuteService($serviceName) {
		$status = true;

		$lastRun = array(
			GC_AFIELD_ERROR => array(
				GC_AFIELD_CODE => self::ErrorUnknown,
				GC_AFIELD_MESSAGE => 'Unknown error'
			),
			GC_AFIELD_HEADERS => array(),
			GC_AFIELD_DATA => null
		);

		$serviceClass = self::FetchService($serviceName);
		if($serviceClass !== false) {
			$status = $serviceClass->run();
			$lastRun = $serviceClass->lastRun();
		} else {
			$status = false;

			$lastRun[GC_AFIELD_ERROR][GC_AFIELD_CODE] = self::ErrorUnknownService;
			$lastRun[GC_AFIELD_ERROR][GC_AFIELD_MESSAGE] = "Service '{$serviceName}' not found";
			$lastRun[GC_AFIELD_HEADERS] = array();
			$lastRun[GC_AFIELD_DATA] = null;
		}

		return $lastRun;
	}
	public static function FetchService($serviceName) {
		$out = false;

		$servicePath = Paths::Instance()->servicePath(\TooBasic\Names::ServiceFilename($serviceName));
		if(is_readable($servicePath)) {
			require_once $servicePath;

			$serviceClassName = \TooBasic\Names::ServiceClass($serviceName);

			if(class_exists($serviceClassName)) {
				$out = new $serviceClassName($serviceName);
			} else {
				throw new Exception("Class '{$serviceClassName}' not found");
			}
		}

		return $out;
	}
}
