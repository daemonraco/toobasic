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
	public function testCorePropsClass() {
		$this->myClassTester('MyClassCoreProps', 'CorePropsClass');
//		$this->myClassTester('MyTool', 'CorePropsClass', 'CoreProps');
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
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my_file'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'MyFile'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my-file'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my file'));

		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my_file_representation'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'MyFileRepresentation'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my-file-representation'));
		$this->assertEquals('MyFileRepresentation', $this->parseByService('ItemRepresentationFilename', 'my file representation'));
	}
	public function testItemsFactoryFilename() {
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my_file'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'MyFile'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my-file'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my file'));

		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my_file_factory'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'MyFileFactory'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my-file-factory'));
		$this->assertEquals('MyFileFactory', $this->parseByService('ItemsFactoryFilename', 'my file factory'));
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
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'MyClass', 'Suffix'), "Method 'TooBasic\Names::ClassNameWithSuffix()' affects values that should stay the same.");
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my_class', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my class', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my-class', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'My Class', 'Suffix'));

		$expected = 'MySuffix';
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'MySuffix', 'Suffix'), "Method 'TooBasic\Names::ClassNameWithSuffix()' affects values that should stay the same.");
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my_suffix', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my suffix', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'my-suffix', 'Suffix'));
		$this->assertEquals($expected, $this->parseBySuffixedService('ClassNameWithSuffix', 'My Suffix', 'Suffix'));
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
		$this->assertEquals($expected, $this->parseByService($func, $expected), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals($expected, $this->parseByService($func, "my_{$suffixName}"), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals($expected, $this->parseByService($func, "my {$suffixName}"), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals($expected, $this->parseByService($func, "my-{$suffixName}"), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals($expected, $this->parseByService($func, ucwords("my {$suffixName}")), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
	}
	protected function myBasicFilenameTester($func) {
		$this->assertEquals('my_file', $this->parseByService($func, 'my_file'), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals('my_file', $this->parseByService($func, 'MyFile'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals('my-file', $this->parseByService($func, 'my-file'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals('my-file', $this->parseByService($func, 'my file'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
	}
	protected function myUpperFilenameTester($func) {
		$this->assertEquals('MyFile', $this->parseByService($func, 'MyFile'), "Method 'TooBasic\Names::{$func}()' affects values that should stay the same.");
		$this->assertEquals('MyFile', $this->parseByService($func, 'my_file'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals('MyFile', $this->parseByService($func, 'my-file'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
		$this->assertEquals('MyFile', $this->parseByService($func, 'my file'), "Method 'TooBasic\Names::{$func}()' made a wrong calculation.");
	}
	protected function parseByService($func, $source) {
		$parsing = $this->getJSONUrl('?service=names&func='.urlencode($func).'&source='.urlencode($source));

		$this->assertTrue(isset($parsing->status), "Call to service 'names' didn't return a status flag.");
		$this->assertTrue($parsing->status, "Call to service 'names' returned a bad status.");

		$this->assertTrue(isset($parsing->data->result), "Call to service 'names' didn't return a parsing result value.");

		return $parsing->data->result;
	}
	protected function parseBySuffixedService($func, $source, $suffix) {
		$parsing = $this->getJSONUrl('?service=names_suffixed&func='.urlencode($func).'&source='.urlencode($source).'&suffix='.urlencode($suffix));

		$this->assertTrue(isset($parsing->status), "Call to service 'names_suffixed' didn't return a status flag.");
		$this->assertTrue($parsing->status, "Call to service 'names_suffixed' returned a bad status.");

		$this->assertTrue(isset($parsing->data->result), "Call to service 'names_suffixed' didn't return a parsing result value.");

		return $parsing->data->result;
	}
	// @}
}
