<?php

/**
 * @file FieldFilter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

/**
 * @class FieldFilterException
 * This exception represent any found error related to field filters.
 */
class FieldFilterException extends \TooBasic\Exception {
	
}

/**
 * @interface FieldFilterInterface
 * This interface is a work-around for strict rules: A static method could not be
 * abstract.
 * For more information see class 'FieldFilter'.
 */
interface FieldFilterInterface {
	//
	// Public class methods.
	/**
	 * This method takes a field's plain value and decodes it.
	 *
	 * @param mixed $in Plain value to decode.
	 * @return mixed Returns a decoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Decode($in);
	/**
	 * This method takes a complex value and encodes it into a plain value.
	 *
	 * @param mixed $in Value to encode.
	 * @return mixed Returns an encoded value.
	 * @throws \TooBasic\Representations\FieldFilterException
	 */
	public static function Encode($in);
	/**
	 * This method allows to know if this filters always requires persistence.
	 *
	 * @retrun boolean Returns TRUE when always requires persistence.
	 */
	public static function ForcePersistence();
}

/**
 * @class FieldFilter
 * @abstract
 * This is a basic abstraction of a table field filter.
 */
abstract class FieldFilter implements FieldFilterInterface {

}
