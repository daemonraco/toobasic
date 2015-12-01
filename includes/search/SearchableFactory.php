<?php

namespace TooBasic\Search;

/**
 * @interface SearchableFactory
 */
interface SearchableFactory {
	//
	// Public methods.
	public function searchableItem($id);
	/**
	 * Provides access to a full list of searchable items.
	 * 
	 * @return \TooBasic\Search\SearchableItem[] Items to be analyzed.
	 */
	public function searchableItems();
	//
	// Public class methods.
	public static function Instance();
}
