<?php

namespace TooBasic;

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
		global $ServiceName;
		//
		// Current action execution.
		$serviceLastRun = self::ExecuteService($ServiceName);
		//
		// Displaying if required.
		if($autoDisplay) {
			header("Content-Type: application/json");

			foreach($serviceLastRun["headers"] as $name => $value) {
				header("{$name}: {$value}");
			}
			unset($serviceLastRun["headers"]);

			$out = json_encode($serviceLastRun);
			if(json_last_error() != JSON_ERROR_NONE) {
				$out = json_encode(array(
					"error" => array(
						"code" => self::ErrorJSONEncode,
						"message" => "Unable to encode outout"
					),
					"data" => null
				));
			}

			echo $out;
		}

		return $serviceLastRun;
	}
	//
	// Public class methods.
	public static function ExecuteService($serviceName) {
		$status = true;

		$lastRun = array(
			"error" => array(
				"code" => self::ErrorUnknown,
				"message" => "Unknown error"
			),
			"headers" => array(),
			"data" => null
		);

		$serviceClass = self::FetchService($serviceName);
		if($serviceClass !== false) {
			$status = $serviceClass->run();
			$lastRun = $serviceClass->lastRun();
		} else {
			$status = false;

			$lastRun["error"]["code"] = self::ErrorUnknownService;
			$lastRun["error"]["message"] = "Service '{$serviceName}' not found";
			$lastRun["headers"] = array();
			$lastRun["data"] = null;
		}

		return $lastRun;
	}
	public static function FetchService($serviceName) {
		$out = false;

		$servicePath = Paths::Instance()->servicePath($serviceName);
		if(is_readable($servicePath)) {
			require_once $servicePath;

			$serviceClassName = (is_numeric($serviceName) ? "N" : "").\TooBasic\classname($serviceName)."Service";

			if(class_exists($serviceClassName)) {
				$out = new $serviceClassName($serviceName);
			} else {
				trigger_error("Class '{$serviceClassName}' not found", E_USER_ERROR);
			}
		}

		return $out;
	}
}
