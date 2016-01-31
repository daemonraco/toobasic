<?php

class TooBasic_PathsTest extends PHPUnit_Framework_TestCase {
	//
	// Set up @{
	protected $_generatedFiles = array();
	protected $_paths = false;
	public function setUp() {
		$this->_paths = \TooBasic\Paths::Instance();

		global $Directories;
		global $Paths;
		//
		// JSON config file.
		$path = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONFIGS]}/phpunit.json";
		file_put_contents($path, '{}');
		$this->_generatedFiles[] = $path;
		//
		// JSON config file.
		$path = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONFIGS]}/phpunit.php";
		file_put_contents($path, "<?php\n");
		$this->_generatedFiles[] = $path;
	}
	public function tearDown() {
		foreach($this->_generatedFiles as $path) {
			unlink($path);
		}
	}
	// @}
	//
	// Basic path searches @{
	public function testJsonConfigurationFile() {
		$path = $this->_paths->configPath('phpunit', \TooBasic\Paths::ExtensionJSON);
		$this->assertTrue($path ? true : false);
		$this->assertTrue(is_file($path));
	}
	public function testPhpConfigurationFile() {
		$path = $this->_paths->configPath('phpunit', \TooBasic\Paths::ExtensionPHP);
		$this->assertTrue($path ? true : false);
		$this->assertTrue(is_file($path));
	}
	// @}
}
