<?php

class ExampleModel extends Model {
	//
	// Public methods.
	public function sayHi() {
		debugit("Hello, I'm ".__CLASS__."!", true);
	}
	//
	// Protected methods.
	protected function init() {
		
	}
}
