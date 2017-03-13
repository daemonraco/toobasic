<?php

use TooBasic\Managers\SearchManager;

/**
 * @class SearchService
 * This service provides access to TooBasic's Search Engine and runs a simple
 * search.
 *
 * Accessible at '?service=search&terms=something'
 */
class SearchService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Protected methods.
	/**
	 * General response handler.
	 *
	 * @return boolean Returns TRUE when there were no errors.
	 */
	protected function basicRun() {
		//
		// Params
		$limit = isset($this->params->get->limit) ? $this->params->get->limit : 0;
		$offset = isset($this->params->get->offset) ? $this->params->get->offset : 0;
		//
		// Searching.
		$info = false;
		$results = SearchManager::Instance()->search($this->params->get->terms, $limit, $offset, null, $info);
		//
		// Counting and flattening items.
		foreach($results as &$item) {
			$item = $item->toArray();
		}
		//
		// Assigning results.
		$this->assign('results', $results);
		$this->assign('count', $info[GC_AFIELD_COUNT]);
		$this->assign('countByType', $info[GC_AFIELD_COUNTS]);
		$this->assign('garbage', $info[GC_AFIELD_GARBAGE]);
		$this->assign('timers', $info[GC_AFIELD_TIMERS]);

		return $this->status();
	}
	/**
	 * Controller's initializer.
	 */
	protected function init() {
		parent::init();
		//
		// Setting cache modifiers.
		$this->_cacheParams['GET'][] = 'limit';
		$this->_cacheParams['GET'][] = 'offset';
		$this->_cacheParams['GET'][] = 'terms';
		//
		// Setting required parameters.
		$this->_requiredParams['GET'][] = 'terms';
	}
}
