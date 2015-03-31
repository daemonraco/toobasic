<?php

namespace TooBasic;

abstract class Controller extends Exporter {
	//
	// Protected properties.
	protected $_layout = null;
	//
	// Magic methods.
	public function __construct($actionName = false) {
		parent::__construct($actionName);

		global $Defaults;
		//
		// It doesn't matter what it's set for the current class, there
		// are rules first.
		if(isset($this->_params->debugnolayout)) {
			//
			// Removing layout setting.
			$this->_layout = false;
		} elseif(isset($_REQUEST["layout"])) {
			//
			// Using forced layout.
			$this->_layout = $_REQUEST["layout"];
		} elseif($this->_layout === null) {
			//
			// If nothing is set, then the default is used.
			//
			// @note: NULL means there's no setting for this controllers layout. FALSE means this controller uses no layout.
			$this->_layout = $Defaults["layout"];
		}
	}
	//
	// Public methods.
	public function insert($actionName) {
		return (string) ActionsManager::InsertAction($actionName);
	}
	public function layout() {
		return $this->_layout;
	}
	//
	// Protected methods.
	protected function autoAssigns() {
		parent::autoAssigns();

		$this->assign("tr", $this->translate);
		$this->assign("ctrl", $this);
	}
}
