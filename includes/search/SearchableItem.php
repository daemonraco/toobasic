<?php

namespace TooBasic\Search;

/**
 * @interface SearchableItem
 * This is the basic interface for any item that can be indexed by TooBasic's
 * Search Engine.
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
	/**
	 * This mehtod returns a specification of grouping criteria to be applied
	 * on some entry.
	 *
	 * @return \stdClass Returns a criteria specification structure.
	 */
	public function criteria();
	/**
	 * This method provides access to current object's id.
	 *
	 * @return int Returns an ID.
	 */
	public function id();
	/**
	 * This method allows to knwon if the current object is flagged as indexed
	 * or not.
	 *
	 * @return boolean Returns TRUE when it's flagged as indexed.
	 */
	public function isIndexed();
	/**
	 * This method allows to modify current object indexed status.
	 *
	 * @param boolean $status Status to be set.
	 * @return boolean Returns the final result.
	 */
	public function setIndexed($status = true);
	/**
	 * This mehtod returns all the values that has to be used as indexation
	 * terms.
	 *
	 * @return string Returns a terms string.
	 */
	public function terms();
	/**
	 * This method provides a to convert an complex item object into a simpler
	 * version of in represented into an array.
	 *
	 * @return mixed[string] Returns a simpler version of the object.
	 */
	public function toArray();
	/**
	 * This method provides the search item type to use when indexing this
	 * object.
	 *
	 * @return string Returns a search item code.
	 */
	public function type();
	/**
	 * This method provides a full URI to access an indexed item.
	 *
	 * @return string Returns a search item full URI.
	 */
	public function viewLink();
}
