<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$factory = $this->representation->filters;

		$items = [];
		foreach($factory->items() as $item) {
			$items[] = $item->toArray();
		}
		$this->assign('items', $items);

		return $this->status();
	}
}
