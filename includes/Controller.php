<?php

namespace TooBasic;

abstract class Controller extends Exporter {
	//
	// Protected properties.
	//
	// Magic methods.
	//
	// Public methods.
	public function insert($actionName) {
		return (string)ActionsManager::InsertAction($actionName);
	}
	//
	// Protected methods.
	protected function autoAssigns() {
		parent::autoAssigns();

		$this->assign("tr", $this->translate);
		$this->assign("ctrl", $this);
	}
}
