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
	// Public methods.
	/**
	 * Provides access to the name of the field that keeps the indexation
	 * status flag.
	 *
	 * @return string Returns a field name.
	 */
	public function indexColumn() {
		return $this->_cp_IndexColumn;
	}
	/**
	 * Provides access to a list of knwon values that represent indexation status.
	 * At least returns two entries 'GC_SEARCH_ENGINE_INDEXED' and
	 * 'GC_SEARCH_ENGINE_UNINDEXED'.
	 *
	 * @return string[string] Returns a set of posible statuses.
	 */
	public function indexStatuses() {
		return [
			GC_SEARCH_ENGINE_INDEXED => 'Y',
			GC_SEARCH_ENGINE_UNINDEXED => 'N'
		];
	}
	/**
	 * Provides access to a stream of items that are pending of indexation.
	 *
	 * @return \TooBasic\Search\SearchableItemRepresentation Returns a stream
	 * of items.
	 */
	public function pendingStream() {
		$statuses = $this->indexStatuses();
		return $this->streamBy([$this->indexColumn() => $statuses[GC_SEARCH_ENGINE_UNINDEXED]]);
	}
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
	/**
	 * Provides access to a full list of searchable items.
	 *
	 * @return \TooBasic\Search\ItemsStream Items to be analyzed.
	 */
	public function searchableStream() {
		return $this->stream();
	}
}
