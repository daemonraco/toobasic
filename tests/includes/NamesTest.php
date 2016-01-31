<?php

class NamesTest extends PHPUnit_Framework_TestCase {
	//
	// Class names @{
	public function testConfigClass() {
		$this->myClassTester('MyClassConfig', 'ConfigClass');
		$this->myClassTester('MyConfig', 'ConfigClass','Config');
	}
	public function testControllerClass() {
		$this->myClassTester('MyClassController', 'ControllerClass');
		$this->myClassTester('MyController', 'ControllerClass','Controller');
	}
	public function testEmailControllerClass() {
		$this->myClassTester('MyClassEmail', 'EmailControllerClass');
		$this->myClassTester('MyEmail', 'EmailControllerClass','Email');
	}
	public function testItemRepresentationClass() {
		$this->myClassTester('MyClassRepresentation', 'ItemRepresentationClass');
		$this->myClassTester('MyRepresentation', 'ItemRepresentationClass','Representation');
	}
	public function testItemsFactoryClass() {
		$this->myClassTester('MyClassFactory', 'ItemsFactoryClass');
		$this->myClassTester('MyFactory', 'ItemsFactoryClass','Factory');
	}
	public function testModelClass() {
		$this->myClassTester('MyClassModel', 'ModelClass');
		$this->myClassTester('MyModel', 'ModelClass','Model');
	}
	public function testServiceClass() {
		$this->myClassTester('MyClassService', 'ServiceClass');
		$this->myClassTester('MyService', 'ServiceClass','Service');
	}
	public function testShellCronClass() {
		$this->myClassTester('MyClassCron', 'ShellCronClass');
		$this->myClassTester('MyCron', 'ShellCronClass','Cron');
	}
	public function testShellSystoolClass() {
		$this->myClassTester('MyClassSystool', 'ShellSystoolClass');
		$this->myClassTester('MySystool', 'ShellSystoolClass','Systool');
	}
	public function testShellToolClass() {
		$this->myClassTester('MyClassTool', 'ShellToolClass');
		$this->myClassTester('MyTool', 'ShellToolClass','Tool');
	}
	// @}
	//
	// Internal methods.
	protected function myClassTester($expected, $func,$suffixName = 'class') {
		$this->assertEquals($expected, TooBasic\Names::$func("my_{$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func("my {$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func("my-{$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func(ucwords("my {$suffixName}")));
	}
}
