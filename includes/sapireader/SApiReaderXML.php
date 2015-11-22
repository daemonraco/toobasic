<?php

/**
 * @file SApiReaderXML.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\SApiReaderException;

/**
 * @class SApiReaderXML
 * This class is the representation of an API reader the expect XML responses.
 */
class SApiReaderXML extends SApiReader {
	//
	// Public methods.
	/**
	 * This is the method in charge of calling the right service using all
	 * given parameters and configuration specified for it.
	 *
	 * @param string $method Name of the service to call.
	 * @param mixed[] $params List of parameters given on the call.
	 * @return \SimpleXMLElement Returns a decoded result of calling a service.
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
			$response = simplexml_load_string($response);
			//
			// Checking error.
			if($response === false) {
				throw new SApiReaderException("Unable to parse response.");
			}
		} else {
			$response = false;
		}

		return $response;
	}
}
