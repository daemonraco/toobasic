<?php

/**
 * @file SearchManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Timer;

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
	 * This method walks over every entry in the indexed items table and
	 * checks they're still in the database. If an item is no longer in the
	 * database it gets removed from the index.
	 *
	 * @return int Returns the count of entries removed.
	 */
	public function cleanUp() {
		//
		// Default values.
		$count = 0;
		//
		// Global dependencies.
		global $Search;
		//
		// Checking each factory.
		$factories = [];
		foreach($Search[GC_SEARCH_ENGINE_FACTORIES] as $type => $factoryClass) {
			$factories[$type] = $factoryClass::Instance();
		}
		//
		// Items table prefixes.
		$itemsPrefixes = [
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'sit_'
		];
		//
		// Creating a query to get all associations.
		$query = $this->_db->queryAdapter()->select('tb_search_items', [], $itemsPrefixes);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Creating a query to remove all current associations.
		$queryD = $this->_db->queryAdapter()->delete('tb_search_items', [
			'type' => 0,
			'id' => 0
			], $itemsPrefixes);
		$stmtD = $this->_db->prepare($queryD[GC_AFIELD_QUERY]);
		//
		// Checking each item.
		$stmt->execute();
		while($row = $stmt->fetch()) {
			$remove = false;
			//
			// Checking if the type is unknown or if the item doesn't
			// exist.
			if(!isset($factories[$row['sit_type']]) || !$factories[$row['sit_type']]->item($row['sit_id'])) {
				$remove = true;
			}
			//
			// Checking if this entry has to be removed.
			if($remove) {
				$queryD[GC_AFIELD_PARAMS][':type'] = $row['sit_type'];
				$queryD[GC_AFIELD_PARAMS][':id'] = $row['sit_id'];
				$stmtD->execute($queryD[GC_AFIELD_PARAMS]);
				$count++;
			}
		}

		return $count;
	}
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
			foreach($factory->searchableStream() as $item) {
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
		$itemsPrefixes = [
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'sit_'
		];
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
			foreach($factory->pendingStream() as $item) {
				//
				// Obtaining information from current item.
				$type = $item->type();
				$id = $item->id();
				$criteria = self::StringCriteria(self::SimplifyCriteria($item->criteria()));
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
				foreach(array_filter($this->getTerms($terms, $criteria, true)) as $term) {
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

		return $indexed;
	}
	/**
	 * This method allows to run a search for a list of terms provided as a
	 * string.
	 *
	 * @param string $termsString Terms to look for.
	 * @param int $limit Maximum number of items to be returned.
	 * @param int $offset Where to start (zero is from the beginning).
	 * @param \stdClass $criteria Set of specific filtering parameters.
	 * @param int $info Returns some search stats information.
	 * @return mixed[string][] Returns a list of lists group by item types.
	 */
	public function search($termsString, $limit = 0, $offset = 0, \stdClass $criteria = null, &$info = false) {
		//
		// Default values.
		$out = [];
		$criteria = is_object($criteria) ? $criteria : (object)[];
		//
		// Timer shortcut;
		$timer = Timer::Instance();
		$timer->start(GC_AFIELD_FULL);
		$timer->start(GC_AFIELD_SEARCH);
		//
		// Expanded list of terms.
		$terms = self::ExpandTerms(self::SanitizeTerms($termsString));
		//
		// Basic search for items.
		$plainResult = $this->plainSearch($terms, $criteria);
		$plainResultCount = count($plainResult[GC_AFIELD_ITEMS]);
		$timer->stop(GC_AFIELD_SEARCH);
		$timer->start(GC_AFIELD_EXPAND);
		//
		// Checking if only a portion is required.
		if($limit > 0) {
			$plainResult[GC_AFIELD_ITEMS] = array_slice($plainResult[GC_AFIELD_ITEMS], $offset, $limit);
		}
		//
		// Expanding all found items into full objects grouped by type.
		$garbageCount = 0;
		$out = $this->expandResult($plainResult, $garbageCount);
		$timer->stop(GC_AFIELD_EXPAND);
		$timer->stop(GC_AFIELD_FULL);
		//
		// Saving some information.
		$info = [
			GC_AFIELD_TERMS => $terms,
			GC_AFIELD_COUNT => $plainResultCount,
			GC_AFIELD_COUNTS => $plainResult[GC_AFIELD_COUNTS],
			GC_AFIELD_LIMIT => $limit,
			GC_AFIELD_OFFSET => $offset,
			GC_AFIELD_TIMERS => [
				GC_AFIELD_FULL => $timer->timer(GC_AFIELD_FULL),
				GC_AFIELD_EXPAND => $timer->timer(GC_AFIELD_EXPAND),
				GC_AFIELD_SEARCH => $timer->timer(GC_AFIELD_SEARCH)
			],
			GC_AFIELD_GARBAGE => $garbageCount
		];

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method transforms a list of terms into their corresponding term
	 * representation. Also, this method can create unknown terms when
	 * indicated.
	 *
	 * @param string[] $terms List of terms to load.
	 * @param string $stringCriteria Set of specific filtering parameters in
	 * string mode.
	 * @param boolean $create Triggers the creation of unknown terms.
	 * @return \TooBasic\Search\SearchTerm[string] Returns a list of found
	 * terms.
	 */
	protected function getTerms($terms, $stringCriteria, $create = false) {
		//
		// Default values.
		$out = [];
		//
		// Terms factory short cut.
		$factory = $this->representation->search_terms(false, 'TooBasic\\Search');
		//
		// Looking for each requested term.
		foreach($terms as $term) {
			//
			// Attempting to uptaing the requeste item.
			$auxTerm = $factory->itemBy(['term' => $term, 'criteria' => $stringCriteria]);
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
				$auxTerm->criteria = $stringCriteria;
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
	 * @param mixed[] $plainResult Plain search results.
	 * @param int $garbageCount Number of lost items.
	 * @return \TooBasic\Search\SearchableItem[] Returns a list of expanded
	 * items.
	 */
	protected function expandResult($plainResult, &$garbageCount = 0) {
		//
		// Default values.
		$out = [];
		$garbageCount = 0;
		//
		// Global dependencies.
		global $Search;
		//
		// List of factory shortcuts.
		$factories = [];
		//
		// Generating temporary list based on search types to use.
		foreach($plainResult[GC_AFIELD_TYPES] as $type) {
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
			// Shortcut.
			$type = $plainItem[GC_AFIELD_TYPE];
			//
			// Expanding only those elements belonging to an existing
			// factory.
			if(isset($factories[$type])) {
				$item = $factories[$type]->searchableItem($plainItem[GC_AFIELD_ID]);
				if($item) {
					$out[] = $item;
				} else {
					$garbageCount++;
				}
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
	 * @param \stdClass $criteria Set of specific filtering parameters.
	 * @return mixed[string] Plain search result.
	 */
	protected function plainSearch($terms, $criteria = false) {
		//
		// Default values.
		$out = [
			GC_AFIELD_ITEMS => [],
			GC_AFIELD_TYPES => []
		];
		//
		// Items table prefixes.
		$itemsPrefixes = [
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'sit_'
		];
		//
		// Terms table prefixes.
		$termsPrefixes = [
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'ste_'
		];
		//
		// Cleaning criterion entries
		if(!is_object($criteria)) {
			$criteria = (object)[];
		}
		$simplifyCriteria = self::SimplifyCriteria($criteria);
		if(empty($simplifyCriteria)) {
			$simplifyCriteria[] = '';
		}
		//
		// Creating a query to search term.
		$queryT = $this->_db->queryAdapter()->select('tb_search_terms', [
			'*:term' => '',
			'*:criteria' => ''
			], $termsPrefixes);
		$stmtT = $this->_db->prepare($queryT[GC_AFIELD_QUERY]);
		//
		// Creating a query to get item associations.
		$queryI = $this->_db->queryAdapter()->select('tb_search_items', [
			'term' => 0,
			], $itemsPrefixes);
		$stmtI = $this->_db->prepare($queryI[GC_AFIELD_QUERY]);

		$termIds = [];
		foreach($simplifyCriteria as $criterion) {
			$queryT[GC_AFIELD_PARAMS][':criteria'] = $criterion ? "%:{$criterion}:%" : '%';

			foreach($terms as $term) {
				$queryT[GC_AFIELD_PARAMS][':term'] = "%{$term}%";
				$stmtT->execute($queryT[GC_AFIELD_PARAMS]);

				foreach($stmtT->fetchAll() as $row) {
					$termIds[] = $row['ste_id'];
				}
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
					$out[GC_AFIELD_ITEMS][$key] = [
						GC_AFIELD_TYPE => $type,
						GC_AFIELD_ID => $id,
						GC_AFIELD_HITS => 1
					];
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
		// Count by types.
		$out[GC_AFIELD_COUNTS] = [];
		foreach($out[GC_AFIELD_TYPES] as $type) {
			$out[GC_AFIELD_COUNTS][$type] = isset($out[GC_AFIELD_COUNTS][$type]) ? $out[GC_AFIELD_COUNTS][$type] + 1 : 1;
		}
		//
		// Cleaning types.
		$out[GC_AFIELD_TYPES] = array_values(array_unique($out[GC_AFIELD_TYPES]));
		//
		// Sorting by hits count (desc).
		usort($out[GC_AFIELD_ITEMS], function($a, $b) {
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
	/**
	 * This method takes a set of filtering criteria and returns in a simpler
	 * way (list of strings).
	 *
	 * @param \stdClass $criteria Set of specific filtering parameters.
	 * @return string[] Returns the filtering parameters in a simpler mode.
	 */
	protected static function SimplifyCriteria(\stdClass $criteria) {
		$out = [];
		//
		// Converting.
		foreach($criteria as $k => $v) {
			$out[] = "{$k}={$v}";
		}

		return $out;
	}
	/**
	 * This method takes a set of filtering criteria given in a simple manner
	 * and retruns it as a simple string.
	 *
	 * @param string[] $simpleCriteria Set of specific filtering parameters in
	 * simple mode.
	 * @return string Returns the filtering parameters as a single string.
	 */
	protected static function StringCriteria($simpleCriteria) {
		//
		// Concatenating criteria.
		$stringCriteria = ':'.implode(':', $simpleCriteria).':';
		//
		// Cleaning multiple consecutive colons.
		while(strpos($stringCriteria, '::') !== false) {
			$stringCriteria = str_replace('::', ':', $stringCriteria);
		}
		//
		// Cleaning the string with has no criterion.
		if(!$stringCriteria == ':') {
			$stringCriteria = '';
		}

		return $stringCriteria;
	}
}
