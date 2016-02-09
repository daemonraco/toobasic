<?php

class Selenium_SystoolTableOnSQLiteTest extends TooBasic_SeleniumTestCase {
	//
	// Internal Properties.
	protected $_fields = array(
		'name' => array(
			'type' => '',
			'value' => 'kitchen',
			'update' => 'bedroom',
			'clear' => true
		),
		'description' => array(
			'type' => ':text',
			'value' => 'This is a place to eat...',
			'update' => 'This is a place to sleep...',
			'clear' => true
		),
		'status' => array(
			'type' => ':enum:AVAILABLE:OCCUPIED:REPAIR:UNKNOWN',
			'value' => 'REPAIR',
			'update' => 'AVAILABLE',
			'clear' => false
		),
		'rank' => array(
			'type' => ':int',
			'value' => 10,
			'update' => 6,
			'clear' => true
		),
		'width' => array(
			'type' => ':float',
			'value' => 5.6,
			'update' => 3.5,
			'clear' => true
		),
		'height' => array(
			'type' => ':float',
			'value' => 3.2,
			'update' => 4.1,
			'clear' => true
		),
		'color' => array(
			'type' => '',
			'value' => 'light-green',
			'update' => 'light-blue',
			'clear' => true
		)
	);
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
		foreach($this->_fields as $field => $conf) {
			$cmd.=" --column {$field}{$conf['type']}";
		}
		passthru($cmd);
		passthru('chmod 0666 '.TESTS_ROOTDIR.'/cache/travis_test.sqlite3');
		//
		// Checking that all expected assets where generated.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			$this->assertTrue(is_file($path), "Asset '{$path}' was not generated.");
		}
	}
	// @}
	//
	// List all view @{
	public function testCheckingEmptyList() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
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
		$addButton = $this->byCssSelector("#MainContents a.btn[href*=\"{$this->_singurlarName}_add\"]");
		$this->assertTrue(boolval($addButton), "There's no button to add a new entry.");
		//
		// Clicking the 'Add' button.
		$addButton->click();
		$this->assertRegExp("/\?action={$this->_singurlarName}_add$/", $this->url(), "There's no button to add a new entry.");
	}
	// @}
	//
	// Adding a new entry @{
	public function testCreatingANewEntryThroughTheForm() {
		$this->url("?action={$this->_singurlarName}_add");
		$this->checkCurrentSource();
		//
		// Default values.
		$inputs = array();
		//
		// Checking buttons.
		$submitButton = $this->byId("{$this->_singurlarName}_form_add");
		$resetButton = $this->byId("{$this->_singurlarName}_form_clear_fields");
		$this->assertTrue(boolval($submitButton), "There's no submit button.");
		$this->assertTrue(boolval($resetButton), "There's no reset button.");
		//
		// Checking inputs, combos and textareas.
		foreach(array_keys($this->_fields) as $field) {
			$inputs[$field] = $this->byId("{$this->_singurlarName}_form_{$field}");
			$this->assertTrue(boolval($inputs[$field]), "There's no way to set field '{$field}'.");
		}
		//
		// Setting a random value and testing reset button.
		foreach($this->_fields as $field => $conf) {
			$inputs[$field]->value($conf['value']);
		}
		$resetButton->click();
		foreach($this->_fields as $field => $conf) {
			$this->assertEmpty($inputs[$field]->value(), "Value for field '{$field}' is not empty and it should after clicking the reset button");
		}
		//
		// Setting values and submitting the new entry.
		foreach($this->_fields as $field => $conf) {
			$inputs[$field]->value($conf['value']);
		}
		$submitButton->click();
		//
		// Checking errors on current page.
		$this->checkCurrentSource();
		//
		// Checking URL.
		$this->assertRegExp("/\?action={$this->_pluralName}$/", $this->url(), "The page didn't return to the main table.");
	}
	// @}
	//
	// Checking table view after insertion @{
	public function testCheckingListWithTheFirstEntry() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
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
		$this->assertEquals(count($rows), 1, "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(count($actionsColumn), 7 + 2, "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(count($actions), 3, "There are more/less action buttons in the last column than expected.");
		$this->assertRegExp("/\?action={$this->_singurlarName}&id=1$/", $actions[0]->attribute('href'), "The 'view' button points to the wrong direction.");
		$this->assertRegExp("/\?action={$this->_singurlarName}_edit&id=1$/", $actions[1]->attribute('href'), "The 'edit' button points to the wrong direction.");
		$this->assertRegExp("/\?action={$this->_singurlarName}_delete&id=1$/", $actions[2]->attribute('href'), "The 'delete' button points to the wrong direction.");
	}
	// @}
	//
	// Checking the 'view' view  @{
	public function testCheckingFirstEntryInformation() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
		//
		// Default values.
		$inputs = array();
		//
		// Checking body.
		$tbody = $this->byCssSelector('#MainContents table tbody ');
		$this->assertTrue(boolval($tbody), "No tbody found.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(count($rows), 1, "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(count($actionsColumn), 7 + 2, "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(count($actions), 3, "There are more/less action buttons in the last column than expected.");
		//
		// Clicking the 'view' button.
		$actions[0]->click();
		$this->checkCurrentSource();
		//
		// Checking current URL.
		$this->assertRegExp("/\?action={$this->_singurlarName}&id=1$/", $this->url(), "This is not the view page for entry with id '1'.");
		//
		// Checking buttons.
		$backButton = $this->byCssSelector("#MainContents a.btn[href*=\"{$this->_pluralName}\"]");
		$this->assertTrue(boolval($backButton), "There's no button to go back.");
		//
		// Checking inputs, combos and textareas.
		foreach(array_keys($this->_fields) as $field) {
			$inputs[$field] = $this->byId("{$this->_singurlarName}_form_{$field}");
			$this->assertTrue(boolval($inputs[$field]), "Field '{$field}' is not shown.");
		}
		//
		// Checking values.
		foreach($this->_fields as $field => $conf) {
			$this->assertEquals($inputs[$field]->value(), $conf['value'], "Value for field '{$field}' is not the one expected.");
		}
		//
		// Going back.
		$backButton->click();
		$this->checkCurrentSource();
		$this->assertRegExp("/\?action={$this->_pluralName}$/", $this->url(), "Page didn't go back to the right place.");
	}
	// @}
	//
	// Checking the 'edit' view  @{
	public function testCheckingFirstEntryEdition() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
		//
		// Default values.
		$inputs = array();
		//
		// Checking body.
		$tbody = $this->byCssSelector('#MainContents table tbody ');
		$this->assertTrue(boolval($tbody), "No tbody found.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(count($rows), 1, "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(count($actionsColumn), 7 + 2, "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(count($actions), 3, "There are more/less action buttons in the last column than expected.");
		//
		// Clicking the 'view' button.
		$actions[1]->click();
		$this->checkCurrentSource();
		//
		// Checking current URL.
		$this->assertRegExp("/\?action={$this->_singurlarName}_edit&id=1$/", $this->url(), "This is not the edit page for entry with id '1'.");
		//
		// Checking buttons.
		$submitButton = $this->byId("{$this->_singurlarName}_form_save");
		$resetButton = $this->byId("{$this->_singurlarName}_form_restore_fields");
		$this->assertTrue(boolval($submitButton), "There's no submit button.");
		$this->assertTrue(boolval($resetButton), "There's no reset button.");
		//
		// Checking inputs, combos and textareas.
		foreach($this->_fields as $field => $conf) {
			$inputs[$field] = $this->byId("{$this->_singurlarName}_form_{$field}");

			$this->assertTrue(boolval($inputs[$field]), "Field '{$field}' is not shown.");
			$this->assertEquals($inputs[$field]->value(), $conf['value'], "Value for field '{$field}' is not the one expected.");
		}
		//
		// Setting new values.
		foreach($this->_fields as $field => $conf) {
			if($conf['clear']) {
				$inputs[$field]->clear();
				$inputs[$field]->value($conf['update']);
			} else {
				$this->select($inputs[$field])->selectOptionByValue($conf['update']);
			}
		}
		//
		// Checking reset button.
		$resetButton->click();
		foreach($this->_fields as $field => $conf) {
			$this->assertEquals($inputs[$field]->value(), $conf['value'], "Value for field '{$field}' is not the one expected.");
		}
		//
		// Setting and submitting new values.
		foreach($this->_fields as $field => $conf) {
			if($conf['clear']) {
				$inputs[$field]->clear();
				$inputs[$field]->value($conf['update']);
			} else {
				$this->select($inputs[$field])->selectOptionByValue($conf['update']);
			}
		}
		$submitButton->click();
		$this->checkCurrentSource();
		$this->assertRegExp("/\?action={$this->_pluralName}$/", $this->url(), "Page didn't go back to the right place.");
	}
	// @}
	//
	// Checking the 'delete' view  @{
	public function testCheckingFirstEntryRemoval() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
		//
		// Default values.
		$inputs = array();
		//
		// Checking body.
		$tbody = $this->byCssSelector('#MainContents table tbody ');
		$this->assertTrue(boolval($tbody), "No tbody found.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(count($rows), 1, "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(count($actionsColumn), 7 + 2, "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(count($actions), 3, "There are more/less action buttons in the last column than expected.");
		//
		// Clicking the 'view' button.
		$actions[2]->click();
		$this->checkCurrentSource();
		//
		// Checking current URL.
		$this->assertRegExp("/\?action={$this->_singurlarName}_delete&id=1$/", $this->url(), "This is not the delete page for entry with id '1'.");
		//
		// Checking buttons.
		$submitButton = $this->byId("{$this->_singurlarName}_form_delete");
		$backButton = $this->byCssSelector("#MainContents a.btn[href*=\"{$this->_pluralName}\"]");
		$this->assertTrue(boolval($submitButton), "There's no submit button.");
		$this->assertTrue(boolval($backButton), "There's no back button.");
		//
		// Checking inputs, combos and textareas.
		foreach($this->_fields as $field => $conf) {
			$inputs[$field] = $this->byId("{$this->_singurlarName}_form_{$field}");

			$this->assertTrue(boolval($inputs[$field]), "Field '{$field}' is not shown.");
			$this->assertEquals($inputs[$field]->value(), $conf['update'], "Value for field '{$field}' is not the one expected.");
		}
		//
		// Submitting the form but canceling in the confirm window.
		$submitButton->click();
		$this->dismissAlert();
		$this->assertRegExp("/\?action={$this->_singurlarName}_delete&id=1$/", $this->url(), "The page changes when it shouldn't.");
		//
		// Submitting the form and acception in the confirm window.
		$submitButton->click();
		$this->acceptAlert();
		$this->checkCurrentSource();
		$this->assertRegExp("/\?action={$this->_pluralName}$/", $this->url(), "Page didn't go back to the right place.");
	}
	// @}
	//
	// List all should be back to square one @{
	public function testCheckingEmptyListAgain() {
		$this->url("?action={$this->_pluralName}");
		$this->checkCurrentSource();
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
	}
	// @}	//
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
			TESTS_ROOTDIR."/cache/system/config-priorities.json",
			TESTS_ROOTDIR."/cache/travis_test.sqlite3",
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
