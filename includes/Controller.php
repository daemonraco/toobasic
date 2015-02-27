<?php

abstract class Controller {
	//
	// Magic methods.
	public function __construct() {
		$this->init();
	}
}
