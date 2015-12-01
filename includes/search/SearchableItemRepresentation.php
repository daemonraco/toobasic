<?php

/**
 * @file SearchableItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Search;

/**
 * @class SearchableItemRepresentation
 * @abstract
 * This abstract class combines a 'ItemRepresentation' with a 'SearchableItem'
 * interface and provides some basic methods required by such interface.
 */
abstract class SearchableItemRepresentation extends \TooBasic\Representations\ItemRepresentation implements \TooBasic\Search\SearchableItem {
	//
	// Protected core properties.
	/**
	 * @var string Name of a field containing names (without prefix).
	 */
	protected $_CP_NameColumn = 'name';
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $dbname Database connection name to which this
	 * representation is associated.
	 */
	public function __construct($dbname) {
		parent::__construct($dbname);
		//
		// Setting column 'indexed' to work as a boolean value.
		$this->_CP_ColumnFilters['indexed'] = GC_DATABASE_FIELD_FILTER_BOOLEAN;
	}
	//
	// Public methods.
	/**
	 * This method provides access to current representation's id.
	 *
	 * @return int Returns an ID.
	 */
	public function id() {
		return $this->id;
	}
	/**
	 * This method allows to knwon if the current representation is flagged as
	 * indexed or not.
	 *
	 * @return boolean Returns TRUE when it's flagged as indexed.
	 */
	public function isIndexed() {
		return $this->indexed;
	}
	/**
	 * This method allows to modify current representation indexed status.
	 *
	 * @param boolean $status Status to be set.
	 * @return boolean Returns the final result.
	 */
	public function setIndexed($status = true) {
		$this->indexed = $status;
		$this->persist();

		return $this->isIndexed();
	}
	/**
	 * This mehtod returns all the values that has to be used as indexation
	 * terms.
	 *
	 * @return string Returns a terms string.
	 */
	public function terms() {
		return $this->{$this->_CP_NameColumn};
	}
	/**
	 * @abstract
	 * This method provides the search item type to use when indexing this
	 * representation.
	 *
	 * @return string Returns a search item code.
	 */
	abstract public function type();
}
