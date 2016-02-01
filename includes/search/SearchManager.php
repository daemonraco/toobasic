<?php

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Search\SearchableItem;

/**
 * @class SearchManager
 * This manager holds the logic to maintain and use search entries.
 */
class SearchManager extends \TooBasic\Managers\Manager {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter Database connection shortcut
	 */
	protected $_db = false;
	/**
	 * @var string Database connection name shortcut.
	 */
	protected $_dbprefix = '';
	//
	// Public methods.
	/**
	 * This method modifies every known searchable item setting it as not
	 * indexed and then triggers a full indexation.
	 *
	 * @return int Returns the amount of indexed items.
	 */
	public function forceFullScan() {
		//
		// Global dependencies.
		global $Search;
		//
		// Checking each factory.
		foreach($Search[GC_SEARCH_ENGINE_FACTORIES] as $factoryClass) {
			$factory = $factoryClass::Instance();
			//
			// Setting each as not yet indexed.
			foreach($factory->searchableItems() as $item) {
				$item->setIndexed(false);
			}
		}
		//
		// Triggering indexation and returning it's result.
		return $this->index();
	}
	/**
	 * This method checks each knwon factory with searchable items and indexes
	 * all of its pending items.
	 *
	 * @return int Returns the amount of indexed items.
	 */
	public function index() {
		//
		// Default values.
		$indexed = 0;
		//
		// Global dependencies.
		global $Search;
		//
		// Items table prefixes.
		$itemsPrefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'sit_'
		);
		//
		// Creating a query to remove all current associations.
		$queryD = $this->_db->queryAdapter()->delete('tb_search_items', [
			'type' => 0,
			'id' => 0
			], $itemsPrefixes);
		$stmtD = $this->_db->prepare($queryD[GC_AFIELD_QUERY]);
		//
		// Creating a query to insert all term associations.
		$queryI = $this->_db->queryAdapter()->insert('tb_search_items', [
			'term' => 0,
			'type' => 0,
			'id' => 0
			], $itemsPrefixes);
		$stmtI = $this->_db->prepare($queryI[GC_AFIELD_QUERY]);
		//
		// Checking each factory.
		foreach($Search[GC_SEARCH_ENGINE_FACTORIES] as $factoryClass) {
			//
			// Factory shortcut.
			$factory = $factoryClass::Instance();
			//
			// Checking each item.
			foreach($factory->searchableItems() as $item) {
				if(!$item->isIndexed()) {
					//
					// Obtaining information from current
					// item.
					$type = $item->type();
					$id = $item->id();
					$terms = self::ExpandTerms(self::SanitizeTerms($item->terms()));
					//
					// Setting parameters.
					$queryD[GC_AFIELD_PARAMS][':type'] = $type;
					$queryI[GC_AFIELD_PARAMS][':type'] = $type;
					$queryD[GC_AFIELD_PARAMS][':id'] = $id;
					$queryI[GC_AFIELD_PARAMS][':id'] = $id;
					//
					// Removing all current associations.
					$stmtD->execute($queryD[GC_AFIELD_PARAMS]);
					//
					// Adding associations.
					foreach($this->getTerms($terms, true) as $term) {
						//
						// Setting parameters.
						$queryI[GC_AFIELD_PARAMS][':term'] = $term->id;
						//
						// Adding.
						$stmtI->execute($queryI[GC_AFIELD_PARAMS]);
					}
					//
					// Setting current item as indexed.
					$item->setIndexed(true);
					//
					// Counting indexed items.
					$indexed++;
				}
			}
		}

		return $indexed;
	}
	/**
	 * This method allows to run a search for a list of terms provided as a
	 * string.
	 *
	 * @param string $termsString Terms to look for.
	 * @return mixed[string][] Returns a list of lists group by item types.
	 */
	public function search($termsString) {
		//
		// Expanded list of terms.
		$terms = self::ExpandTerms(self::SanitizeTerms($termsString));
		//
		// Basic search for items.
		$plainResult = $this->plainSearch($terms);
		//
		// Expanding all found items into full objects grouped by type and
		// returning it as result.
		return $this->expandResult($plainResult);
	}
	//
	// Protected methods.
	/**
	 * This method transforms a list of terms into their corresponding term
	 * representation. Also, this method can create unknown terms when
	 * indicated.
	 *
	 * @param string[] $terms
	 * @param boolean $create Triggers the creation of unknown terms.
	 * @return type
	 */
	protected function getTerms($terms, $create = false) {
		//
		// Default values.
		$out = array();
		//
		// Terms factory short cut.
		$factory = $this->representation->search_terms(false, 'TooBasic\\Search');
		//
		// Looking for each requested term.
		foreach($terms as $term) {
			//
			// Attempting to uptaing the requeste item.
			$auxTerm = $factory->itemByName($term);
			//
			// If it doesn't exist and it's required to be created, it
			// is.
			if(!$auxTerm && $create) {
				//
				// Creating a new empty term.
				$id = $factory->create();
				//
				// Loading it.
				$auxTerm = $factory->item($id);
				//
				// Setting basic values.
				$auxTerm->term = $term;
				$auxTerm->count = 0;
				//
				// Persisting changes.
				$auxTerm->persist();
			}
			//
			// If a term was found it's add to the list of results.
			if($auxTerm) {
				$out[$term] = $auxTerm;
			}
		}

		return $out;
	}
	/**
	 * This method takes a plain search result and expands each item grouping
	 * them by item type.
	 *
	 * @param type $plainResult
	 * @return type
	 */
	protected function expandResult($plainResult) {
		//
		// Default values.
		$out = array();
		//
		// Global dependencies.
		global $Search;
		//
		// List of factory shortcuts.
		$factories = array();
		//
		// Generating temporary list based on search types to use.
		foreach($plainResult[GC_AFIELD_TYPES] as $type) {
			//
			// Creating groups by type.
			$out[$type] = array();
			//
			// Appending a factory shortcut.
			if(isset($Search[GC_SEARCH_ENGINE_FACTORIES][$type])) {
				$class = $Search[GC_SEARCH_ENGINE_FACTORIES][$type];
				$factories[$type] = $class::Instance();
			}
		}
		//
		// Expanding each result.
		foreach($plainResult[GC_AFIELD_ITEMS] as $plainItem) {
			//
			// Expanding only those elements belonging to an existing
			// factory.
			if(isset($factories[$plainItem[GC_AFIELD_TYPE]])) {
				$out[$type][] = $factories[$plainItem[GC_AFIELD_TYPE]]->searchableItem($plainItem[GC_AFIELD_ID]);
			}
		}

		return $out;
	}
	/**
	 * Class initializer.
	 */
	protected function init() {
		parent::init();
		//
		// Generating shortcuts.
		$this->_db = DBManager::Instance()->getDefault();
		$this->_dbprefix = $this->_db->prefix();
	}
	/**
	 * This method performs a basic search for a list of terms and returns the
	 * result in a simple structure sorted by hit counts.
	 *
	 * @param string[] $terms List of terms to look for.
	 * @return mixed[string] Plain search result.
	 */
	protected function plainSearch($terms) {
		//
		// Default values.
		$out = array(
			GC_AFIELD_ITEMS => array(),
			GC_AFIELD_TYPES => array()
		);
		//
		// Items table prefixes.
		$itemsPrefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'sit_'
		);
		//
		// Terms table prefixes.
		$termsPrefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'ste_'
		);
		//
		// Creating a query to search term.
		$queryT = $this->_db->queryAdapter()->select('tb_search_terms', [
			'*term' => '',
			], $termsPrefixes);
		$stmtT = $this->_db->prepare($queryT[GC_AFIELD_QUERY]);
		//
		// Creating a query to get item associations.
		$queryI = $this->_db->queryAdapter()->select('tb_search_items', [
			'term' => 0,
			], $itemsPrefixes);
		$stmtI = $this->_db->prepare($queryI[GC_AFIELD_QUERY]);

		$termIds = array();
		foreach($terms as $term) {
			$queryT[GC_AFIELD_PARAMS][':term'] = "%{$term}%";
			$stmtT->execute($queryT[GC_AFIELD_PARAMS]);

			foreach($stmtT->fetchAll() as $row) {
				$termIds[] = $row['ste_id'];
			}
		}
		$termIds = array_unique($termIds);
		//
		// Retreiving item associations.
		foreach($termIds as $term) {
			$queryI[GC_AFIELD_PARAMS][':term'] = $term;
			$stmtI->execute($queryI[GC_AFIELD_PARAMS]);
			//
			// Analyzing each found item.
			foreach($stmtI->fetchAll() as $row) {
				//
				// Basic vaues.
				$type = $row['sit_type'];
				$id = $row['sit_id'];
				$key = "{$type}-{$id}";
				//
				// Checking if current result was already found
				// and therefore increase the hits count or start
				// counting for it.
				if(isset($out[GC_AFIELD_ITEMS][$key])) {
					$out[GC_AFIELD_ITEMS][$key][GC_AFIELD_HITS] ++;
				} else {
					$out[GC_AFIELD_ITEMS][$key] = array(
						GC_AFIELD_TYPE => $type,
						GC_AFIELD_ID => $id,
						GC_AFIELD_HITS => 1
					);
				}
				//
				// Keeping track of all found types.
				$out[GC_AFIELD_TYPES][] = $type;
			}
		}
		//
		// Cleaning items.
		$out[GC_AFIELD_ITEMS] = array_values($out[GC_AFIELD_ITEMS]);
		//
		// Cleaning types.
		$out[GC_AFIELD_TYPES] = array_unique($out[GC_AFIELD_TYPES]);
		//
		// Sorting by hits count (desc).
		uasort($out[GC_AFIELD_ITEMS], function($a, $b) {
			return $a[GC_AFIELD_HITS] < $b[GC_AFIELD_HITS];
		});

		return $out;
	}
	// 
	// Public class methods.
	/**
	 * This class method takes a terms string and removes all unwanted
	 * characters.
	 *
	 * @param string $termsString String to clean up.
	 * @return string Returns a clean terms string.
	 */
	public static function SanitizeTerms($termsString) {
		return preg_replace('/([-_\\(\\)\\[\\]\\/\\\\]+)/', ' ', strtolower($termsString));
	}
	// 
	// Protected class methods.
	/**
	 * This class method takes a clean terms string and expand it into a clean
	 * list of terms.
	 *
	 * @param string $termsString String to expand.
	 * @return string[] List of terms.
	 */
	protected static function ExpandTerms($termsString) {
		return array_unique(array_filter(explode(' ', $termsString)));
	}
}
