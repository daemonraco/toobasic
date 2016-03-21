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
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$items = $this->representation->data->items();
		$itemsList = array();
		foreach($items as $item) {
			$itemsList[] = $item->toArray();
		}

		$this->assign('count', count($items));
		$this->assign('items', $itemsList);

		return $this->status();
	}
}
