<?php

class TooBasic_PathsTest extends TooBasic_TestCase {
	//
	// Set up @{
	protected $_paths = false;
	public function setUp() {
		$this->_paths = \TooBasic\Paths::Instance();
		$this->loadAssetsOf(__FILE__);
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
	//
	// Image files searches @{
	public function testImageFilesSearches() {
		$path = $this->_paths->imagePath('phpunit_image', 'jpg');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_image\.jpg$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Model files searches @{
	public function testModelFilesSearches() {
		$path = $this->_paths->modelPath('PhpUnit');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/PhpUnit\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Representation files searches @{
	public function testRepresentationFilesSearches() {
		$path = $this->_paths->representationPath('PhpUnitRepresentation');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/PhpUnitRepresentation\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Service files searches @{
	public function testServiceFilesSearches() {
		$path = $this->_paths->servicePath('phpunit_service');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_service\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Shell Tools files searches @{
	public function testShellToolFilesSearches() {
		$path = $this->_paths->shellTool('phpunit_tool');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_tool\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testShellCronFilesSearches() {
		$path = $this->_paths->shellCron('phpunit_cron');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_cron\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testShellSystoolFilesSearches() {
		$path = $this->_paths->shellSys('phpunit_systool');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_systool\.php$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Snippet files searches @{
	public function testSnippetFilesSearches() {
		$path = $this->_paths->snippetPath('phpunit_snippet');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_snippet\.html$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Template files searches @{
	public function testTemplateFilesSearches() {
		$path = $this->_paths->templatePath('phpunit_template');
		$this->assertTrue($path ? true : false, 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertTrue(preg_match('#(.*)/phpunit_template\.html$#', $path) ? true : false, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
}