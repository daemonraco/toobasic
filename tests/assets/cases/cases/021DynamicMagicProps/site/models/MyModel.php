<?php

class MyModel extends \TooBasic\Model {
	public function methodMS() {
		return "I'm '".__CLASS__.'::'.__FUNCTION__."()' calling '\$this->ms->getClassName()': ".$this->ms->getClassName();
	}
	public function methodMYS() {
		return "I'm '".__CLASS__.'::'.__FUNCTION__."()' calling '\$this->mys->getClassName()': ".$this->mys->getClassName();
	}
	protected function init() {
		
	}
}
