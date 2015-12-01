<?php

/**
 * @file SearchableItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Search;

/**
 * @class SearchableItemRepresentation
 * @abstract
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

		$this->_CP_ColumnFilters['indexed'] = GC_DATABASE_FIELD_FILTER_BOOLEAN;
	}
	//
	// Public methods.
	public function id() {
		return $this->id;
	}
	public function isIndexed() {
		return $this->indexed;
	}
	public function setIndexed($status = true) {
		$this->indexed = $status;
		$this->persist();

		return $this->isIndexed();
	}
	public function terms() {
		return $this->{$this->_CP_NameColumn};
	}
	abstract public function type();
}
