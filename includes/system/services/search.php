<?php

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
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	/**
	 * General response handler.
	 *
	 * @return boolean Returns TRUE when there were no errors.
	 */
	protected function basicRun() {
		//
		// Searching.
		$results = \TooBasic\Managers\SearchManager::Instance()->search($this->params->get->terms);
		//
		// Counting and flattening items.
		$fullCount = 0;
		$countByType = [];
		foreach($results as $type => &$items) {
			if(!isset($countByType[$type])) {
				$countByType[$type] = 0;
			}

			foreach($items as &$item) {
				$item = $item->toArray();
				$countByType[$type] ++;
				$fullCount++;
			}
		}
		//
		// Assigning results.
		$this->assign('results', $results);
		$this->assign('count', $fullCount);
		$this->assign('countByType', $countByType);

		return $this->status();
	}
	/**
	 * Controller's initializer.
	 */
	protected function init() {
		parent::init();
		//
		// Setting cache modifiers.
		$this->_cacheParams['GET'][] = 'terms';
		//
		// Setting required parameters.
		$this->_requiredParams['GET'][] = 'terms';
	}
}
