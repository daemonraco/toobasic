<?php

abstract class Controller extends Exporter {
	//
	// Protected properties.
	//
	// Magic methods.
	//
	// Public methods.
	//
	// Protected methods.
	protected function autoAssigns() {
		parent::autoAssigns();

		$this->assign("tr", $this->translate);
	}
}
