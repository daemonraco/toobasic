<?php

namespace TooBasic\Search;

/**
 * @interface SearchableItem
 */
interface SearchableItem {
	//
	// Magic methods.
	/**
	 * This magic method allows to directly print the object.
	 *
	 * @return string Pretty formatted string with basic information item.
	 */
	public function __toString();
	//
	// Public methods.
	public function id();
	public function isIndexed();
	public function setIndexed($status = true);
	public function terms();
	public function type();
}
