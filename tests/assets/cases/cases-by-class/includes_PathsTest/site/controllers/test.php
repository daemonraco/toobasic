<?php

/**
 * @class TestController
 *
 * Accessible at '?action=test'
 */
class TestController extends \TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('testConfigurationFileSearches', $this->paths->configPath('phpunit_conf'));
		$this->assign('testPhpConfigurationFileSearches', $this->paths->configPath('phpunit_conf', \TooBasic\Paths::EXTENSION_PHP));
		$this->assign('testJsonConfigurationFileSearches', $this->paths->configPath('phpunit_conf', \TooBasic\Paths::EXTENSION_JSON));
		$this->assign('testControllerFilesSearches', $this->paths->controllerPath('phpunit_ctrl'));
		$this->assign('testCustomFilesSearches', $this->paths->customPaths('phpunit_custom', 'phpunit_cust', 'ext'));
		$this->assign('testEmailFilesSearches', $this->paths->emailControllerPath('phpunit_mail'));
		$this->assign('testStyleFilesSearches', $this->paths->cssPath('phpunit_style'));
		$this->assign('testScriptFilesSearches', $this->paths->jsPath('phpunit_script'));
		$this->assign('testDatabaseSpecificationFilesSearches', $this->paths->dbSpecPaths());
		$this->assign('testDatabaseCallbackFilesSearches', $this->paths->dbSpecCallbackPaths('phpunit_callback'));
		$this->assign('testImageFilesSearches', $this->paths->imagePath('phpunit_image', 'jpg'));
		$this->assign('testModelFilesSearches', $this->paths->modelPath('PhpUnit'));
		$this->assign('testModelFilesSearches', $this->paths->modelPath('PhpUnit'));
		$this->assign('testRepresentationFilesSearches', $this->paths->representationPath('PhpUnitRepresentation'));
		$this->assign('testServiceFilesSearches', $this->paths->servicePath('phpunit_service'));
		$this->assign('testShellToolFilesSearches', $this->paths->shellTool('phpunit_tool'));
		$this->assign('testShellCronFilesSearches', $this->paths->shellCron('phpunit_cron'));
		$this->assign('testShellSystoolFilesSearches', $this->paths->shellSys('phpunit_systool'));
		$this->assign('testSnippetFilesSearches', $this->paths->snippetPath('phpunit_snippet'));
		$this->assign('testTemplateFilesSearches', $this->paths->templatePath('phpunit_template'));
		$this->assign('testDisabledPathsSearches', $this->paths->configPath('phpunit_disabled'));

		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
