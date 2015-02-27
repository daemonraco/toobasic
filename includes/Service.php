<?php

abstract class Service {
	//
	// Magic methods.
	public function __construct() {
		$this->init();
	}
}
