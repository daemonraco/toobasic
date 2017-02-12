<?php

/**
 * @file SearchableFactory.php
 * @author Alejandro Dario Simi
 */

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
	 * Provides access to the name of the field that keeps the indexation
	 * status flag.
	 *
	 * @return string Returns a field name.
	 */
	public function indexColumn();
	/**
	 * Provides access to a list of knwon values that represent indexation status.
	 * At least returns two entries 'GC_SEARCH_ENGINE_INDEXED' and
	 * 'GC_SEARCH_ENGINE_UNINDEXED'.
	 *
	 * @return string[string] Returns a set of posible statuses.
	 */
	public function indexStatuses();
	/**
	 * Provides access to a stream of items that are pending of indexation.
	 *
	 * @return \TooBasic\Search\SearchableItemRepresentation Returns a stream
	 * of items.
	 */
	public function pendingStream();
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
	/**
	 * Provides access to a full list of searchable items.
	 *
	 * @return \TooBasic\Search\ItemsStream Items to be analyzed.
	 */
	public function searchableStream();
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
