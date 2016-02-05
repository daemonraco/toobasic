<?php

class TooBasic_NamesTest extends TooBasic_TestCase {
	//
	// Class names @{
	public function testConfigClass() {
		$this->myClassTester('MyClassConfig', 'ConfigClass');
		$this->myClassTester('MyConfig', 'ConfigClass', 'Config');
	}
	public function testControllerClass() {
		$this->myClassTester('MyClassController', 'ControllerClass');
		$this->myClassTester('MyController', 'ControllerClass', 'Controller');
	}
	public function testEmailControllerClass() {
		$this->myClassTester('MyClassEmail', 'EmailControllerClass');
		$this->myClassTester('MyEmail', 'EmailControllerClass', 'Email');
	}
	public function testItemRepresentationClass() {
		$this->myClassTester('MyClassRepresentation', 'ItemRepresentationClass');
		$this->myClassTester('MyRepresentation', 'ItemRepresentationClass', 'Representation');
	}
	public function testItemsFactoryClass() {
		$this->myClassTester('MyClassFactory', 'ItemsFactoryClass');
		$this->myClassTester('MyFactory', 'ItemsFactoryClass', 'Factory');
	}
	public function testModelClass() {
		$this->myClassTester('MyClassModel', 'ModelClass');
		$this->myClassTester('MyModel', 'ModelClass', 'Model');
	}
	public function testServiceClass() {
		$this->myClassTester('MyClassService', 'ServiceClass');
		$this->myClassTester('MyService', 'ServiceClass', 'Service');
	}
	public function testShellCronClass() {
		$this->myClassTester('MyClassCron', 'ShellCronClass');
		$this->myClassTester('MyCron', 'ShellCronClass', 'Cron');
	}
	public function testShellSystoolClass() {
		$this->myClassTester('MyClassSystool', 'ShellSystoolClass');
		$this->myClassTester('MySystool', 'ShellSystoolClass', 'Systool');
	}
	public function testShellToolClass() {
		$this->myClassTester('MyClassTool', 'ShellToolClass');
		$this->myClassTester('MyTool', 'ShellToolClass', 'Tool');
	}
	// @}
	//
	// Filename names @{
	public function testConfigFilename() {
		$this->myBasicFilenameTester('ConfigFilename');
	}
	public function testControllerFilename() {
		$this->myBasicFilenameTester('ControllerFilename');
	}
	public function testEmailControllerFilename() {
		$this->myBasicFilenameTester('EmailControllerFilename');
	}
	public function testItemRepresentationFilename() {
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my_file'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('MyFile'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my-file'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my file'));

		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my_file_representation'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('MyFileRepresentation'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my-file-representation'));
		$this->assertEquals('MyFileRepresentation', TooBasic\Names::ItemRepresentationFilename('my file representation'));
	}
	public function testItemsFactoryFilename() {
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my_file'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('MyFile'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my-file'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my file'));

		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my_file_factory'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('MyFileFactory'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my-file-factory'));
		$this->assertEquals('MyFileFactory', TooBasic\Names::ItemsFactoryFilename('my file factory'));
	}
	public function testModelFilename() {
		$this->myUpperFilenameTester('ModelFilename');
	}
	public function testServiceFilename() {
		$this->myBasicFilenameTester('ServiceFilename');
	}
	public function testShellCronFilename() {
		$this->myBasicFilenameTester('ShellCronFilename');
	}
	public function testShellSysFilename() {
		$this->myBasicFilenameTester('ShellSysFilename');
	}
	public function testShellToolFilename() {
		$this->myBasicFilenameTester('ShellToolFilename');
	}
	// @}
	//
	// Generic methods @{
	public function testClassName() {
		$this->myClassTester('MyClass', 'ClassName');
	}
	public function testClassNameWithSuffix() {
		$expected = 'MyClassSuffix';
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('MyClass', 'Suffix'), "Method 'TooBasic\Names::ClassNameWithSuffix()' affects values that should stay the same.");
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my_class', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my class', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my-class', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('My Class', 'Suffix'));

		$expected = 'MySuffix';
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('MySuffix', 'Suffix'), "Method 'TooBasic\Names::ClassNameWithSuffix()' affects values that should stay the same.");
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my_suffix', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my suffix', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('my-suffix', 'Suffix'));
		$this->assertEquals($expected, TooBasic\Names::ClassNameWithSuffix('My Suffix', 'Suffix'));
	}
	public function testFilename() {
		$this->myUpperFilenameTester('Filename');
	}
	public function testSnakeFilename() {
		$this->myBasicFilenameTester('SnakeFilename');
	}
	// @}
	//
	// Internal methods @{
	protected function myClassTester($expected, $func, $suffixName = 'class') {
		$this->assertEquals($expected, TooBasic\Names::$func($expected), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals($expected, TooBasic\Names::$func("my_{$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func("my {$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func("my-{$suffixName}"));
		$this->assertEquals($expected, TooBasic\Names::$func(ucwords("my {$suffixName}")));
	}
	protected function myBasicFilenameTester($func) {
		$this->assertEquals('my_file', TooBasic\Names::{$func}('my_file'), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals('my_file', TooBasic\Names::{$func}('MyFile'));
		$this->assertEquals('my-file', TooBasic\Names::{$func}('my-file'));
		$this->assertEquals('my-file', TooBasic\Names::{$func}('my file'));
	}
	protected function myUpperFilenameTester($func) {
		$this->assertEquals('MyFile', TooBasic\Names::{$func}('MyFile'), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals('MyFile', TooBasic\Names::{$func}('my_file'));
		$this->assertEquals('MyFile', TooBasic\Names::{$func}('my-file'));
		$this->assertEquals('MyFile', TooBasic\Names::{$func}('my file'));
	}
	// @}
}
