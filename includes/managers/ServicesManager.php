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
	// Protected methods.
	protected function explainInterface($serviceName) {
		$out = array(
			"status" => true,
			"interface" => null,
			"headers" => array(),
			"error" => false,
			"errors" => array()
		);

		$out["interface"] = array(
			"name" => $serviceName,
			"cached" => false,
			"methods" => array()
		);

		$serviceFile = $this->paths->servicePath($serviceName);
		$service = false;
		if($serviceFile && is_readable($serviceFile)) {
			$pathinfo = pathinfo($serviceFile);

			$service = array(
				"path" => $serviceFile,
				"name" => $pathinfo["filename"],
				"class" => classname($pathinfo["filename"]).GC_CLASS_SUFFIX_SERVICE
			);
		} else {
			$error = array(
				"code" => self::ErrorUnknownService,
				"message" => "Service '{$serviceName}' not found",
				"location" => array(
					"method" => __CLASS__."::".__FUNCTION__."()",
					"file" => __FILE__,
					"line" => __LINE__
				)
			);

			$out["status"] = false;
			$out["interface"] = false;
			$out["error"] = $error;
			$out["errors"][] = $error;
		}

		if($service) {
			require_once $service["path"];

			$object = new $service["class"]();

			$out["interface"]["required_params"] = $object->requiredParams();
			$out["interface"]["cached"] = $object->cached();
			$out["interface"]["cache_params"] = $object->cacheParams();

			foreach(get_class_methods($object) as $name) {
				if(preg_match("/^run(?<method>[A-Z]*)$/", $name, $matches)) {
					$matches["method"] = $matches["method"] ? $matches["method"] : "GET";
					$out["interface"]["methods"][] = $matches["method"];
				}
			}

			foreach($out["interface"]["required_params"] as $method => $value) {
				if(!$value) {
					unset($out["interface"]["required_params"][$method]);
				}
			}
		}

		return $out;
	}
	protected function explainInterfaces() {
		$out = array(
			"status" => true,
			"services" => array(),
			"headers" => array(),
			"error" => false,
			"errors" => array()
		);

		foreach($this->paths->servicePath("*", true) as $serviceFile) {
			$pathinfo = pathinfo($serviceFile);

			$service = $this->explainInterface($pathinfo["filename"]);
			$out["services"][] = $service["interface"];
		}

		return $out;
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

			$serviceClassName = (is_numeric($serviceName) ? "N" : "").\TooBasic\classname($serviceName).GC_CLASS_SUFFIX_SERVICE;

			if(class_exists($serviceClassName)) {
				$out = new $serviceClassName($serviceName);
			} else {
				trigger_error("Class '{$serviceClassName}' not found", E_USER_ERROR);
			}
		}

		return $out;
	}
}
