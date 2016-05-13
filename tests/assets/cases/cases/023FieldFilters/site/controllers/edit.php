<?php

/**
 * @class EditController
 *
 * Accessible at '?action=edit'
 */
class EditController extends \TooBasic\Controller {
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
		$this->assign('before', $items);

		$item = $factory->item(2);
		$item->status = !$item->status;
		$item->props->newProp = 10.3;
		$item->persist();

		$items = [];
		foreach($factory->items() as $item) {
			$items[] = $item->toArray();
		}
		$this->assign('after', $items);

		return $this->status();
	}
}
