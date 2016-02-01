<?php

/**
 * @file SearchableItemsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Search;

/**
 * @class SearchableItemsFactory
 * @abstract
 * This abstract class combines a 'ItemsFactory' with a 'SearchableFactory'
 * interface and provides some basic methods required by such interface.
 */
abstract class SearchableItemsFactory extends \TooBasic\Representations\ItemsFactory implements \TooBasic\Search\SearchableFactory {
	//
	// Protected core properties.
	/**
	 * @var string Name of a field containing names (without prefix).
	 */
	protected $_CP_NameColumn = 'name';
	//
	// Public methods.
	/**
	 * Provides access to a specific searchable items.
	 *
	 * @param int $id Item's id to look for.
	 * @return \TooBasic\Search\SearchableItem Items to be analyzed.
	 */
	public function searchableItem($id) {
		return $this->item($id);
	}
	/**
	 * Provides access to a full list of searchable items.
	 *
	 * @return \TooBasic\Search\SearchableItem[] Items to be analyzed.
	 */
	public function searchableItems() {
		return $this->items();
	}
}
