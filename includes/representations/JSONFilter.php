<?php

/**
 * @file JSONFilter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use TooBasic\Representations\FieldFilterException;

/**
 * @class JSONFilter
 * This filter manages table field values as JSON objects.
 */
class JSONFilter extends FieldFilter {
	//
	// Public class methods.
	/**
	 * This method takes a field's plain value and decodes it into a
	 * 'stdClass' object.
	 *
	 * @param mixed $in Plain value to decode.
	 * @return \stdClass Returns a decoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Decode($in) {
		$out = false;

		if(is_null($in) || $in === '') {
			$out = new \stdClass();
		} else {
			$out = json_decode($in);
			if(!$out) {
				throw new FieldFilterException("Unable to decode field value (".json_last_error_msg().").");
			}
		}

		return $out;
	}
	/**
	 * This method takes a `stdClass` object and flattens it into a plain
	 * value.
	 *
	 * @param \stdClass $in Value to encode.
	 * @return string Returns an encoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Encode($in) {
		if(is_null($in) || $in === '') {
			$in = new \stdClass();
		}

		$out = json_encode($in);
		if(!$out) {
			throw new FieldFilterException("Unable to encode field value (".json_last_error_msg().").");
		}

		return $out;
	}
	/**
	 * This method allows to know if this filters always requires persistence.
	 *
	 * @retrun boolean This filter always requires persistence.
	 */
	public static function ForcePersistence() {
		return true;
	}
}
