<?php

/**
 * @file SApiReader.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\MagicProp;

/**
 * @class SApiReaderException
 */
class SApiReaderException extends \TooBasic\Exception {
	
}

/**
 * @class SApiReader
 * This class is the basic representation of an API reader.
 * All the responses it can get are returned as they were read.
 * 
 * Debugs:
 * 	- debugsar: Prompts several debug entries with basic information.
 * 	- debugsarconf: Shows the configuration to be used.
 */
class SApiReader {
	//
	// Protected properties.
	/**
	 * @var \stdClass This property hold the API configuration use by this
	 * instance.
	 */
	protected $_config = false;
	/**
	 * @var boolean This flag indicates if basic debug messages has to be
	 * prompted or not.
	 */
	protected $_debug = false;
	/**
	 * @var \TooBasic\MagicProp MagicProp singleton shortcut.
	 */
	protected $_magic = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param \stdClass $json API configuration to use.
	 */
	public function __construct(\stdClass $json) {
		//
		// Shortcuts.
		$this->_config = $json;
		$this->_magic = MagicProp::Instance();
		//
		// Checking debug state.
		$this->_debug = isset($this->_magic->params->debugsar);
		//
		// Expanding JSON specification.
		$this->expand();
		//
		// Debugging configuration.
		if(isset($this->_magic->params->debugsarconf)) {
			\TooBasic\debugThing($this->_config);
		}
		//
		// Validating JSON specification.
		$this->validate();
	}
	/**
	 * This method allows to call a specified services without parameters as
	 * if it were a property of the current class.
	 *
	 * @param string $method Name of the service to call.
	 * @return string Returns the result of calling a service.
	 * @throws SApiReaderException
	 */
	public function __get($method) {
		//
		// Forwarding the call.
		return $this->call($method);
	}
	/**
	 * This method allows to call a specified services as if it were a method
	 *  of the current class.
	 *
	 * @param string $method Name of the service to call.
	 * @param mixed[] $params List of parameters given on the call.
	 * @return string Returns the result of calling a service.
	 * @throws SApiReaderException
	 */
	public function __call($method, $params) {
		//
		// Forwarding the call.
		return $this->call($method, $params);
	}
	//
	// Public methods.
	/**
	 * This is the method in charge of calling the right service using all
	 * given parameters and configuration specified for it.
	 *
	 * @param string $method Name of the service to call.
	 * @param mixed[] $params List of parameters given on the call.
	 * @return string Returns the result of calling a service.
	 * @throws SApiReaderException
	 */
	public function call($method, $params = array()) {
		//
		// Default values.
		$response = false;
		//
		// Checking that it's a knwon service.
		if(isset($this->_config->services->{$method})) {
			//
			// Service configuration shortcut.
			$methConf = $this->_config->services->{$method};
			//
			// List of parameters to use.
			$callParams = array();
			//
			// Copying default values.
			foreach($methConf->defaults as $k => $v) {
				$callParams[$k] = $v;
			}
			//
			// Copying given parameters.
			$names = array_values($methConf->params);
			$values = array_values($params);
			while($names) {
				if(!isset($values[0])) {
					break;
				}
				$callParams[array_shift($names)] = array_shift($values);
			}
			//
			// Checking parameters count.
			if(count($methConf->params) > count($callParams)) {
				throw new SApiReaderException("Not all required parameters where given.");
			}
			//
			// Building params to send on POST.
			$sendParams = array();
			foreach($methConf->sendParams as $name => $from) {
				//
				// Checking that it's been given.
				if(!isset($callParams[$from])) {
					throw new SApiReaderException("POST parameter '{$name}' requires parameter {$from} but it was not specified.");
				}
				$sendParams[$name] = $callParams[$from];
			}
			//
			// Building URL.
			$url = $this->_config->url.str_replace(array_keys($callParams), array_values($callParams), $methConf->uri);
			//
			// Requesting data.
			$response = $this->getContent($methConf->method, $url, $sendParams);
		} else {
			throw new SApiReaderException("Unknown service called '{$method}'.");
		}

		return $response;
	}
	//
	// Protected methods.
	/**
	 * This method ensures the presence of some required field inside the
	 * configuration with at least some default values and types.
	 */
	protected function expand() {
		//
		// 'abstract' must be present and be a boolean.
		if(!isset($this->_config->abstract) || !is_bool($this->_config->abstract)) {
			$this->_config->abstract = false;
		}
		//
		// 'headers' must be present and be an object.
		if(!isset($this->_config->headers) || !is_object($this->_config->headers)) {
			$this->_config->headers = new \stdClass();
		}
		//
		// Expanding each service specification.
		if(isset($this->_config->services) && is_object($this->_config->services)) {
			foreach($this->_config->services as &$srv) {
				//
				// 'method' must exist and be a string.
				if(!isset($srv->method) || !is_string($srv->method)) {
					$srv->method = 'GET';
				}
				//
				// ... also upper-case.
				$srv->method = strtoupper($srv->method);
				//
				// 'params' must exist and be a list.
				if(!isset($srv->params) || !is_array($srv->params)) {
					$srv->params = array();
				}
				//
				// 'sendParams' must exist and be an object.
				if(!isset($srv->sendParams) || !is_object($srv->sendParams)) {
					$srv->sendParams = new \stdClass();
				}
				//
				// 'defaults' must exist and be an object.
				if(!isset($srv->defaults) || !is_object($srv->defaults)) {
					$srv->defaults = new \stdClass();
				}
			}
		}
	}
	/**
	 * This is the method that performs the actual REST operation.
	 * It requires cURL library to be installed.
	 *
	 * @param string $method Name of the service to call.
	 * @param string $url Full URL to call.
	 * @param string[string] $sendParams List of parameters so send on a POST
	 * request.
	 * @return string Returns the result of calling a service.
	 * @throws SApiReaderException
	 */
	protected function getContent($method, $url, $sendParams) {
		//
		// Checking library.
		if(!function_exists('curl_init')) {
			throw new SApiReaderException($this->_magic->tr->EX_class_requires_library([
				'class' => __CLASS__,
				'requirement' => 'cURL'
			]));
		}
		//
		// Generating headers.
		$headers = array();
		foreach($this->_config->headers as $k => $v) {
			$headers[] = "{$k}: {$v}";
		}
		//
		// Create cURL resource.
		$ch = \curl_init();
		//
		// Setting cURL options.
		\curl_setopt($ch, CURLOPT_URL, $url);
		\curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		\curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		\curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		/** @FIXME CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER should not be used unless configured in the api JSON file. */
		\curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		\curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//
		// Selecting the method.
		switch($method) {
			case 'POST':
				//
				// Setting POST cURL options.
				\curl_setopt($ch, CURLOPT_POST, true);
				\curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sendParams));
				break;
			case 'GET':
			default:
		}
		//
		// Rerieving output.
		$out = \curl_exec($ch);
		//
		// Debugging call.
		if($this->_debug) {
			$info = [
				'url' => $url,
				'method' => $method,
				'curl' => [
					'errno' => \curl_errno($ch),
					'error' => \curl_error($ch)
				],
				'response-code' => \curl_getinfo($ch, CURLINFO_HTTP_CODE),
				'response-headers' => \curl_getinfo($ch, CURLINFO_HEADER_OUT)
			];
			if($headers) {
				$info['headers'] = $headers;
			}
			if($method != 'GET') {
				$info['params'] = $sendParams;
			}

			\TooBasic\debugThing($info);
		}
		/** @TODO check http errors based on configuration per service. \curl_getinfo($ch, CURLINFO_HTTP_CODE) */
		//
		// Closing cURL resource to free up system resources.
		\curl_close($ch);

		return $out;
	}
	/**
	 * This method validates the structure of the configuration and triggers
	 * exceptions when something wrong is found.
	 *
	 * @throws SApiReaderException
	 */
	protected function validate() {
		//
		// Checking abstraction flag.
		//
		// Checking main fields presence.
		foreach(array('services', 'url', 'name', 'description') as $field) {
			if(!isset($this->_config->{$field})) {
				throw new SApiReaderException("Configuration field '{$field}' is not present.");
			}
		}
		//
		// Checking services.
		if(!is_object($this->_config->services)) {
			throw new SApiReaderException("Configuration field 'services' is not an object.");
		}
		if(!boolval(get_object_vars($this->_config->services))) {
			throw new SApiReaderException("Configuration field 'services' is empty.");
		}
		//
		// Checking each service.
		$requiredFields = ['uri'];
		foreach($this->_config->services as $name => $srv) {
			//
			// Checking required fields.
			foreach($requiredFields as $field) {
				if(!isset($srv->{$field})) {
					throw new SApiReaderException("Configuration of service '{$name}' lacks field '{$field}'.");
				}
			}
			//
			// Checking that every parameters to be send on POST
			// request is required.
			foreach($srv->sendParams as $name => $from) {
				if(!in_array($from, $srv->params)) {
					throw new SApiReaderException("POST Parameter '{$name}' takes its value from a non required parameter called '{$from}'.");
				}
			}
		}
	}
}
