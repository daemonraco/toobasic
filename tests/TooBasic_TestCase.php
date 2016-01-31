<?php

class TooBasic_TestCase extends PHPUnit_Framework_TestCase {
	//
	// Set up @{
	protected $_generatedDirectories = array();
	protected $_generatedFiles = array();
	public function setUp() {
		//
		// Creating directories.
		foreach($this->_generatedDirectories as $pos => $path) {
			if(is_dir($path)) {
				unset($this->_generatedDirectories[$pos]);
			} else {
				mkdir($path);
			}
		}
		$this->_generatedDirectories = array_values($this->_generatedDirectories);
		//
		// Creating files.
		foreach($this->_generatedFiles as $path) {
			touch($path);
		}
	}
	public function tearDown() {
		foreach($this->_generatedFiles as $path) {
			unlink($path);
		}
		foreach(array_reverse($this->_generatedDirectories) as $path) {
			rmdir($path);
		}
	}
	// @}
}
