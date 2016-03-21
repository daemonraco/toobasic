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
		global $Connections;

		$this->assign('db_name', $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_DB]);

		$masters = array();
		foreach($this->model->entries->getMasters() as $master) {
			$masters[] = json_encode($master);
		}
		$this->assign('masters', $masters);

		$children = array();
		foreach($this->model->entries->getChildren() as $child) {
			$children[] = json_encode($child);
		}
		$this->assign('children', $children);

		$entries = array();
		foreach($this->model->entries->getEntries() as $entry) {
			$entries[] = json_encode($entry);
		}
		$this->assign('entries', $entries);

		$searchPattern = 'eor';
		$search = array();
		foreach($this->model->entries->getEntries($searchPattern) as $entry) {
			$search[] = json_encode($entry);
		}
		$this->assign('searchPattern', $searchPattern);
		$this->assign('search', $search);

		return $this->status();
	}
}
