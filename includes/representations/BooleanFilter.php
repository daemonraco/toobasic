<?php

/**
 * @file BooleanFilter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

/**
 * @class BooleanFilter
 * This filter manages table field values as boolean.
 */
class BooleanFilter extends FieldFilter {
	//
	// Constants.
	const VALUE_TRUE = 'Y';
	const VALUE_FALSE = 'N';
	//
	// Public class methods.
	/**
	 * This method takes a field's plain value and decodes it into a
	 * 'boolean'.
	 *
	 * @param mixed $in Plain value to decode.
	 * @return boolean Returns a decoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Decode($in) {
		return ($in == self::VALUE_TRUE);
	}
	/**
	 * This method takes a `boolean` and encodes it into a plain value.
	 *
	 * @param boolean $in Value to encode.
	 * @return string Returns an encoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Encode($in) {
		return \boolval($in) ? self::VALUE_TRUE : self::VALUE_FALSE;
	}
	/**
	 * This method allows to know if this filters always requires persistence.
	 *
	 * @retrun boolean This filter not always requires persistence.
	 */
	public static function ForcePersistence() {
		return false;
	}
}
