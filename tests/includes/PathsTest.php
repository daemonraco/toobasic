<?php

class TooBasic_PathsTest extends TooBasic_TestCase {
	//
	// Set up @{
	protected $_paths = false;
	public function setUp() {
		$this->_paths = \TooBasic\Paths::Instance();

		global $Directories;
		global $Paths;
		//
		// Config files.
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONFIGS]}/phpunit_conf.json";
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONFIGS]}/phpunit_conf.php";
		//
		// Controller file.
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CONTROLLERS]}/phpunit_ctrl.php";
		//
		// Custom file.
		$dirpath = "{$Directories[GC_DIRECTORIES_SITE]}/phpunit_custom";
		$this->_generatedDirectories[] = $dirpath;
		$this->_generatedFiles[] = "{$dirpath}/phpunit_cust.ext";
		//
		// Email file.
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_EMAIL_CONTROLLERS]}/phpunit_mail.php";
		//
		// Style file.
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_CSS]}/phpunit_style.css";
		//
		// Script file.
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_JS]}/phpunit_script.js";
		//
		// Database file.
		$path = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_DBSPECS]}/phpunit_table.json";
		file_put_contents($path, '{}');
		$this->_generatedFiles[] = $path;
		$this->_generatedFiles[] = "{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_DBSPECSCALLBACK]}/phpunit_callback.sql";

		parent::setUp();
	}
	// @}
	//
	// Configuration files searches @{
	public function testConfigurationFileSearches() {
		$path = $this->_paths->configPath('phpunit_conf');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_conf\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testPhpConfigurationFileSearches() {
		$path = $this->_paths->configPath('phpunit_conf', \TooBasic\Paths::ExtensionPHP);
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_conf\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testJsonConfigurationFileSearches() {
		$path = $this->_paths->configPath('phpunit_conf', \TooBasic\Paths::ExtensionJSON);
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_conf.json$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Controller files searches @{
	public function testControllerFilesSearches() {
		$path = $this->_paths->controllerPath('phpunit_ctrl');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_ctrl\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Custom files searches @{
	public function testCustomFilesSearches() {
		$path = $this->_paths->customPaths('phpunit_custom', 'phpunit_cust', 'ext');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_custom/phpunit_cust\.ext$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Email files searches @{
	public function testEmailFilesSearches() {
		$path = $this->_paths->emailControllerPath('phpunit_mail');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_mail\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Style files searches @{
	public function testStyleFilesSearches() {
		$path = $this->_paths->cssPath('phpunit_style');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_style\.css$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Script files searches @{
	public function testScriptFilesSearches() {
		$path = $this->_paths->jsPath('phpunit_script');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_script\.js$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Database files searches @{
	public function testDatabaseSpecificationFilesSearches() {
		$paths = $this->_paths->dbSpecPaths();
		$this->assertTrue(is_array($paths));
		//
		// Searching amount the results.
		$found = false;
		foreach($paths as $path) {
			if(preg_match('#(.*)/phpunit_table\.json$#', $path)) {
				$found = $path;
				break;
			}
		}
		$this->assertTrue($found ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($found), "Found path '{$found}' is not a file.");
	}
	public function testDatabaseCallbackFilesSearches() {
		$path = $this->_paths->dbSpecCallbackPaths('phpunit_callback');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_callback\.sql$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
}
