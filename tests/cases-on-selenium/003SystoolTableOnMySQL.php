<?php

class Selenium_SystoolTableOnMySQLTest extends TooBasic_SeleniumTestCase {
	//
	// Internal Properties.
	protected $_moduleName = 'mymodule';
	protected $_pluralName = 'people';
	protected $_singurlarName = 'person';
	//
	// Set up @{
	public function setUp() {
		$this->loadAssetsOf(__FILE__);
		parent::setUp();
	}
	// @}
	//
	// Table creation @{
	public function testCreatingTableUsingSystoolTable() {
		$cmd = "php shell.php sys table create {$this->_singurlarName}";
		$cmd.=" --plural {$this->_pluralName}";
		$cmd.=" --module {$this->_moduleName}";
		$cmd.=" --bootstrap";
		$cmd.=" --column name";
		$cmd.=" --column description:text";
		$cmd.=" --column status:enum:AVAILABLE:OCCUPIED:REPAIR:UNKNOWN";
		$cmd.=" --column rank:int";
		$cmd.=" --column width:float";
		$cmd.=" --column height:float";
		$cmd.=" --column color";
		passthru($cmd);
		//
		// Checking that all expected assets where generated.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			$this->assertTrue(is_file($path), "Asset '{$path}' was not generated.");
		}
	}
	// @}
	//
	// Table creation @{
	public function testCheckingEmptyList() {
		$this->url("?action={$this->_pluralName}");
		//
		// Checking table.
		$table = $this->byCssSelector('#MainContents table');
		$this->assertTrue(boolval($table), "No table found inside element '#MainContents'.");
		//
		// Checking header
		$thead = $table->byTag('thead');
		$this->assertTrue(boolval($thead), "Table has no header.");

		$headers = $thead->elements($this->using('css selector')->value('th'));
		$this->assertEquals(count($headers), 7 + 2, "There are more/less headers than expected.");
		//
		// Checking body.
		$tbody = $table->byTag('tbody');
		$this->assertTrue(boolval($tbody), "Table has no body.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(count($rows), 0, "At this point there shouldn't be any entry.");
		//
		// Checking buttons.
		$addButton = $this->byCssSelector('#MainContents a.btn[href*="person_add"]');
		$this->assertTrue(boolval($addButton), "There's no button to add a new entry.");
		//
		// Clicking the 'Add' button.
		$addButton->click();
		$this->assertRegExp('/\?action=person_add$/', $this->url(), "There's no button to add a new entry.");
	}
	// @}
	//
	// Table removal @{
	public function testRemovingTableUsingSystoolTable() {
		$cmd = "php shell.php sys table remove {$this->_singurlarName}";
		$cmd.=" --plural {$this->_pluralName}";
		$cmd.=" --module {$this->_moduleName}";
		passthru($cmd);
		//
		// Checking that all expected assets where removed except those
		// that are generic.
		$acceptableAssets = array(
			TESTS_ROOTDIR."/modules/mymodule/configs/routes.json",
			TESTS_ROOTDIR."/modules/mymodule/langs/en_us.json"
		);
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			if(in_array($path, $acceptableAssets)) {
				continue;
			}
			$this->assertNotTrue(is_file($path), "Asset '{$path}' was not removed.");
		}
	}
	// @}
}
