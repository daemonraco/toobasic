<?php

//use TooBasic\Managers\RestManager;

class TestService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$factory = $this->representation->tests;

		$mode = $this->params->get->mode;

		switch($mode) {
			case 'idby':
				$id = $factory->idBy(['name' => $this->params->get->name]);
				if($id) {
					$this->assign('id', $id);
				}
				break;
			case 'byname':
				$item = $factory->itemBy(['name' => $this->params->get->name]);
				if($item) {
					$this->assign('item', $item->toArray());
				}
				break;
			case 'list_stream':
				$items = [];
				foreach($factory->stream() as $item) {
					$items[] = $item->toArray();
				}
				$this->assign('items', $items);
				break;
			case 'list':
			default:
				$items = [];
				foreach($factory->items() as $item) {
					$items[] = $item->toArray();
				}
				$this->assign('items', $items);
				break;
		}

		return $this->status();
	}
	protected function init() {
		parent::init();
		$this->_requiredParams['GET'][] = 'mode';
//		$this->_requiredParams['GET'][] = 'type';
	}
}
