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
 */
class SApiReaderJSON extends SApiReader {
	//
	// Public methods.
	public function call($method, $params = array()) {
		$response = parent::call($method, $params);

		if($response) {
			$json = json_decode($response);
			if(!$json) {
				debugit($response,1);
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
