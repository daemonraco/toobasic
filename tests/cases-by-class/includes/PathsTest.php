<?php

class TooBasic_PathsTest extends TooBasic_TestCase {
	//
	// Configuration files searches @{
	public function testConfigurationFileSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/configs/phpunit_conf\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testPhpConfigurationFileSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/configs/phpunit_conf\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testJsonConfigurationFileSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/configs/phpunit_conf\.json$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Controller files searches @{
	public function testControllerFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/controllers/phpunit_ctrl\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Custom files searches @{
	public function testCustomFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/phpunit_custom/phpunit_cust\.ext$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Email files searches @{
	public function testEmailFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/emails/phpunit_mail\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Style files searches @{
	public function testStyleFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/styles/phpunit_style\.css$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Script files searches @{
	public function testScriptFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/scripts/phpunit_script\.js$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Database files searches @{
	public function testDatabaseSpecificationFilesSearches() {
		$paths = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(is_array($paths));
		//
		// Searching amount the results.
		$found = false;
		foreach($paths as $path) {
			if(preg_match('#(.*)/site/db/phpunit_table\.json$#', $path)) {
				$found = $path;
				break;
			}
		}
		$this->assertTrue(boolval($found), 'Test file not found.');
		$this->assertTrue(is_file($found), "Found path '{$found}' is not a file.");
	}
	public function testDatabaseCallbackFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/db/phpunit_callback\.sql$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Image files searches @{
	public function testImageFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/images/phpunit_image\.jpg$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Model files searches @{
	public function testModelFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/models/PhpUnit\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Representation files searches @{
	public function testRepresentationFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/models/representations/PhpUnitRepresentation\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Service files searches @{
	public function testServiceFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/services/phpunit_service\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Shell Tools files searches @{
	public function testShellToolFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/shell/tools/phpunit_tool\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testShellCronFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/shell/crons/phpunit_cron\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	public function testShellSystoolFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/shell/sys/phpunit_systool\.php$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Snippet files searches @{
	public function testSnippetFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/snippets/phpunit_snippet\.html$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Template files searches @{
	public function testTemplateFilesSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertTrue(boolval($path), 'Test file not found.');
		$this->assertTrue(is_file($path), "Found path '{$path}' is not a file.");
		$this->assertRegExp('#(.*)/site/templates/action/phpunit_template\.html$#', $path, "Found path '{$path}' does not match the expected pattern.");
	}
	// @}
	//
	// Disabled paths searches @{
	public function testDisabledPathsSearches() {
		$path = $this->getPathFor(__FUNCTION__);
		$this->assertNotTrue(boolval($path), "Test file was found at '{$path}' and it shouldn't.");
	}
	// @}
	//
	// Internal methods @{
	protected function getPathFor($funcName) {
		static $json = false;
		if($json === false) {
			$html = $this->getUrl('?action=test&format=json');
			$json = json_decode($html);
			$this->assertTrue(boolval($json), 'Unable to obtain a valid JSON rsponse from test action.');
		}

		$this->assertTrue(isset($json->{$funcName}), "Test action didn't set a value for '{$funcName}'.");
		return $json->{$funcName};
	}
	// @}
}
