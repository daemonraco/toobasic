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
 */
class SApiReaderXML extends SApiReader {
	//
	// Public methods.
	public function call($method, $params = array()) {
		$response = parent::call($method, $params);

		if($response) {
			$response = simplexml_load_string($response);
			if($response === false) {
				throw new SApiReaderException("Unable to parse response.");
			}
		} else {
			$response = false;
		}

		return $response;
	}
}
