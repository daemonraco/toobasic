<?php

/**
 * @class TestsController
 *
 * Accessible at '?action=tests'
 */
class TestsController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false; # \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	//
	// Protected methods.
	protected function basicRun() {
		$factory = $this->representation->tests;

		$items = array();
		foreach($factory->items() as $item) {
			$items[] = $item->toArray();
		}
		$this->assign("items", $items);

		return $this->status();
	}
}
