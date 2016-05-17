<?php

namespace TooBasic\Search;

/**
 * @interface SearchableFactory
 * This is the basic interface for any factory that can provide items indexable by
 * TooBasic's Search Engine.
 */
interface SearchableFactory {
	//
	// Public methods.
	/**
	 * Provides access to a specific searchable items.
	 *
	 * @param int $id Item's id to look for.
	 * @return \TooBasic\Search\SearchableItem Items to be analyzed.
	 */
	public function searchableItem($id);
	/**
	 * Provides access to a full list of searchable items.
	 *
	 * @return \TooBasic\Search\SearchableItem[] Items to be analyzed.
	 */
	public function searchableItems();
	//
	// Public class methods.
	/**
	 * This method is the main access to a factory and it always returns the
	 * single instance of it.
	 *
	 * @return \TooBasic\Search\SearchableFactory Returns an instance of the
	 * required class.
	 */
	public static function Instance();
}
