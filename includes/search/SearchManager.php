<?php

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Search\SearchableItem;

/**
 * @class SearchManager
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
	public function index() {
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
					$item->setIndexed(true);

					$indexed++;
				}
			}
		}

		return $indexed;
	}
	public function search($termsString) {
		$out = array();

		$terms = self::ExpandTerms(self::SanitizeTerms($termsString));
		$plainResult = $this->plainSearch($terms);
		$out = $this->expandResult($plainResult);

		return $out;
	}
	//
	// Protected methods.
	protected function getTerms($terms, $create = false) {
		$out = array();

		$factory = $this->representation->search_terms(false, 'TooBasic\\Search');
		foreach($terms as $term) {
			$auxTerm = $factory->itemByName($term);
			if(!$auxTerm && $create) {
				$id = $factory->create();
				$auxTerm = $factory->item($id);
				$auxTerm->term = $term;
				$auxTerm->count = 0;
				$auxTerm->persist();
			}

			if($auxTerm) {
				$out[$term] = $auxTerm;
			}
		}

		return $out;
	}
	/**
	 * Class initializer.
	 */
	protected function expandResult($plainResult) {
		$out = array();
		//
		// Global dependencies.
		global $Search;

		$factories = array();
		foreach($plainResult[GC_AFIELD_TYPES] as $type) {
			$out[$type] = array();

			if(isset($Search[GC_SEARCH_ENGINE_FACTORIES][$type])) {
				$class = $Search[GC_SEARCH_ENGINE_FACTORIES][$type];
				$factories[$type] = $class::Instance();
			}
		}

		foreach($plainResult[GC_AFIELD_ITEMS] as $plainItem) {
			if(isset($factories[$plainItem[GC_AFIELD_TYPE]])) {
				$out[$type][] = $factories[$plainItem[GC_AFIELD_TYPE]]->searchableItem($plainItem[GC_AFIELD_ID]);
			}
		}

		return $out;
	}
	protected function init() {
		parent::init();
		//
		// Generating shortcuts.
		$this->_db = DBManager::Instance()->getDefault();
		$this->_dbprefix = $this->_db->prefix();
	}
	protected function plainSearch($terms) {
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

			foreach($stmtI->fetchAll() as $row) {
				$type = $row['sit_type'];
				$id = $row['sit_id'];
				$key = "{$type}-{$id}";

				if(isset($out[GC_AFIELD_ITEMS][$key])) {
					$out[GC_AFIELD_ITEMS][$key][GC_AFIELD_HITS] ++;
				} else {
					$out[GC_AFIELD_ITEMS][$key] = array(
						GC_AFIELD_TYPE => $type,
						GC_AFIELD_ID => $id,
						GC_AFIELD_HITS => 1
					);
				}

				$out[GC_AFIELD_TYPES][] = $type;
			}
		}
		$out[GC_AFIELD_ITEMS] = array_values($out[GC_AFIELD_ITEMS]);
		$out[GC_AFIELD_TYPES] = array_unique($out[GC_AFIELD_TYPES]);

		uasort($out[GC_AFIELD_ITEMS], function($a, $b) {
			return $a[GC_AFIELD_HITS] < $b[GC_AFIELD_HITS];
		});

		return $out;
	}
	// 
	// Public class methods.
	public static function SanitizeTerms($termsString) {
		$out = strtolower($termsString);
		$out = preg_replace('/([-]+)/', ' ', $out);

		return $out;
	}
	// 
	// Protected class methods.
	protected static function ExpandTerms($termsString) {
		return array_unique(array_filter(explode(' ', $termsString)));
	}
}
