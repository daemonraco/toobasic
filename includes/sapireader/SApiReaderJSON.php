<?php

/**
 * @file SApiReaderJSON.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\SApiReaderException;

/**
 * @class SApiReaderJSON
 * This class is the representation of an API reader the expect JSON responses.
 */
class SApiReaderJSON extends SApiReader {
	//
	// Public methods.
	/**
	 * This is the method in charge of calling the right service using all
	 * given parameters and configuration specified for it.
	 *
	 * @param string $method Name of the service to call.
	 * @param mixed[] $params List of parameters given on the call.
	 * @return \stdClass Returns a decoded result of calling a service.
	 * @throws SApiReaderException
	 */
	public function call($method, $params = array()) {
		//
		// Forwarding the call.
		$response = parent::call($method, $params);
		//
		// If there's a response it should be decoded.
		if($response) {
			//
			// Decoding.
			$json = json_decode($response);
			//
			// Checking error.
			if(!$json) {
				throw new SApiReaderException("Unable to parse response (".json_last_error_msg().").");
			} else {
				$response = $json;
			}
		} else {
			$response = false;
		}

		return $response;
	}
}
