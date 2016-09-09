<?php

abstract class Selenium_SystoolTableOnDatabaseTest extends TooBasic_SeleniumTestCase {
	//
	// Internal Properties.
	protected $_acceptableAssets = array();
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
		parent::setUp();

		$this->_acceptableAssets[] = TESTS_ROOTDIR."/cache/system/config-priorities.json";
		$this->_acceptableAssets[] = TESTS_ROOTDIR."/modules/mymodule/configs/config.php";
		$this->_acceptableAssets[] = TESTS_ROOTDIR."/modules/mymodule/configs/routes.json";
		$this->_acceptableAssets[] = TESTS_ROOTDIR."/modules/mymodule/langs/en_us.json";
	}
	// @}
	//
	// Table creation @{
	public function testCreatingTableUsingSystoolTable() {
		$cmd = "php shell.php sys table create {$this->_singurlarName}";
		$cmd.= " --plural {$this->_pluralName}";
		$cmd.= " --module {$this->_moduleName}";
		$cmd.= ' --bootstrap';
		foreach($this->_fields as $field => $conf) {
			$cmd.=" --column {$field}{$conf['type']}";
		}
		$cmd.= ' --searchable person';
		$cmd.= ' --name-field name';
		$cmd.= ' --autocomplete';
		$cmd.= ' --type mysql';
		$this->runCommand($cmd);
		//
		// Checking that all expected assets where generated.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			if(preg_match('~\.sqlite3~', $path)) {
				continue;
			}
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
		$this->assertEquals(7 + 2, count($headers), "There are more/less headers than expected.");
		//
		// Checking body.
		$tbody = $table->byTag('tbody');
		$this->assertTrue(boolval($tbody), "Table has no body.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(0, count($rows), "At this point there shouldn't be any entry.");
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
			if($conf['clear']) {
				$inputs[$field]->value($conf['value']);
			} else {
				$this->select($inputs[$field])->selectOptionByValue($conf['value']);
			}
		}
		$resetButton->click();
		foreach($this->_fields as $field => $conf) {
			$this->assertEmpty($inputs[$field]->value(), "Value for field '{$field}' is not empty and it should after clicking the reset button");
		}
		//
		// Setting values and submitting the new entry.
		foreach($this->_fields as $field => $conf) {
			if($conf['clear']) {
				$inputs[$field]->value($conf['value']);
			} else {
				$this->select($inputs[$field])->selectOptionByValue($conf['value']);
			}
		}
		$submitButton->click();
		//
		// Checking errors on current page.
		$this->checkCurrentSource();
		//
		// Checking URL.
		$this->assertRegExp("/\?action={$this->_pluralName}([#]?)$/", $this->url(), "The page didn't return to the main table.");
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
		$this->assertEquals(7 + 2, count($headers), "There are more/less headers than expected.");
		//
		// Checking body.
		$tbody = $table->byTag('tbody');
		$this->assertTrue(boolval($tbody), "Table has no body.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(1, count($rows), "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(7 + 2, count($actionsColumn), "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(3, count($actions), "There are more/less action buttons in the last column than expected.");
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
		$this->assertEquals(1, count($rows), "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(7 + 2, count($actionsColumn), "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(3, count($actions), "There are more/less action buttons in the last column than expected.");
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
			$this->assertEquals($conf['value'], $inputs[$field]->value(), "Value for field '{$field}' is not the one expected.");
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
		$this->assertEquals(1, count($rows), "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(7 + 2, count($actionsColumn), "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(3, count($actions), "There are more/less action buttons in the last column than expected.");
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
			$this->assertEquals($conf['value'], $inputs[$field]->value(), "Value for field '{$field}' is not the one expected.");
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
			$this->assertEquals($conf['value'], $inputs[$field]->value(), "Value for field '{$field}' is not the one expected.");
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
		$this->assertRegExp("/\?action={$this->_pluralName}([#]?)$/", $this->url(), "Page didn't go back to the right place.");
	}
	// @}
	//
	// Tesing Searchable Entries @{
	public function testUpdatingSearchIndex() {
		$this->runCommand('php shell.php cron search --update');
	}
	public function testSearchingForTheEntryThroughServices() {
		$json = $this->getJSONUrl("?service=search&terms={$this->_fields['name']['update']}");

		$this->assertTrue(isset($json->data), "Response doesn't have a 'data' field.");
		$this->assertTrue(is_object($json->data), "Response 'data' field is not an object.");

		$this->assertTrue(isset($json->data->results), "Response 'data' field doesn't have a 'results' sub-field.");
		$this->assertTrue(isset($json->data->count), "Response 'data' field doesn't have a 'count' sub-field.");
		$this->assertTrue(isset($json->data->countByType), "Response 'data' field doesn't have a 'countByType' sub-field.");

		$this->assertTrue(is_integer($json->data->count), "Sub-field 'data->count' is not an integer.");

		$this->assertEquals(1, $json->data->count, "At least one value should had been found.");

		if($json->data->count == 1) {
			$this->assertTrue(is_object($json->data->results), "Sub-field 'data->results' is not an object.");

			$this->assertTrue(isset($json->data->results->PERSON), "There's no sub-field 'data->results->PERSON'.");
			$this->assertTrue(is_array($json->data->results->PERSON), "Sub-field 'data->results->PERSON' is not a list.");
			$this->assertEquals($json->data->count, count($json->data->results->PERSON), "Sub-field 'data->results->PERSON' has an anexpected amount of items.");

			$this->assertTrue(is_object($json->data->results->PERSON[0]), "The entry in sub-field 'data->results->PERSON' is not an object.");
			foreach($this->_fields as $field => $conf) {
				$this->assertTrue(isset($json->data->results->PERSON[0]->{$field}), "The entry in sub-field 'data->results->PERSON' doesn't have a property called '{$field}'.");
				$this->assertEquals($conf['update'], $json->data->results->PERSON[0]->{$field}, "The entry in sub-field 'data->results->PERSON' has an unexpected value for property '{$field}'.");
			}

			$this->assertTrue(is_object($json->data->countByType), "Sub-field 'data->countByType' is not an object.");
			$this->assertTrue(isset($json->data->countByType->PERSON), "Count by type doesn't have a value for the entry's type.");
			$this->assertEquals(1, $json->data->countByType->PERSON, "Specific count for the entry's type has an unexpected value.");
		}
	}
	public function testSearchingForPartOfTheNameThroughServices() {
		$terms = substr($this->_fields['name']['update'], 2, strlen($this->_fields['name']['update']) - 3);
		$json = $this->getJSONUrl("?service=search&terms={$terms}");

		$this->assertTrue(isset($json->data), "Response doesn't have a 'data' field.");
		$this->assertTrue(is_object($json->data), "Response 'data' field is not an object.");
		$this->assertTrue(isset($json->data->countByType), "Response 'data' field doesn't have a 'countByType' sub-field.");
		$this->assertTrue(is_object($json->data->countByType), "Sub-field 'data->countByType' is not an object.");
		$this->assertTrue(isset($json->data->countByType->PERSON), "Count by type doesn't have a value for the entry's type.");
		$this->assertEquals(1, $json->data->countByType->PERSON, "Specific count for the entry's type has an unexpected value.");
	}
	public function testUsingThePredictiveSearchService() {
		$transaction = rand(0, 1000);
		$term = $this->_fields['name']['update'];
		$json = $this->getJSONUrl("?service={$this->_pluralName}_predictive&pattern={$term}&transaction={$transaction}");

		$this->assertTrue(isset($json->status), "Response doesn't have a field called 'status'.");
		$this->assertTrue($json->status, "Field 'status' is not ok.");

		$this->assertTrue(isset($json->data), "Response doesn't have a field called 'data'.");
		$this->assertTrue(is_object($json->data), "Field 'data' is not an object.");

		$this->assertTrue(isset($json->transaction), "Response doesn't have a field called 'transaction'.");
		$this->assertEquals($transaction, $json->transaction, "Field 'transaction' has an unexpected value.");

		$this->assertTrue(isset($json->data->pattern), "Response data doesn't have a field called 'pattern'.");
		$this->assertEquals("%{$term}%", $json->data->pattern, "Response data field 'pattern' has an unexpected value.");

		$this->assertTrue(isset($json->data->items), "Response data doesn't have a field called 'items'.");
		$this->assertTrue(is_array($json->data->items), "Response data field 'items' is not a list.");
		$this->assertEquals(1, count($json->data->items), "Service found an unexpected amount of items.");
		$this->assertTrue(is_object($json->data->items[0]), "First items is not an object.");

		foreach($this->_fields as $field => $conf) {
			$this->assertTrue(isset($json->data->items[0]->{$field}), "Found item doesn't have a property called '{$field}'.");
			$this->assertEquals($conf['update'], $json->data->items[0]->{$field}, "Found item's property '{$field}' has an anexpected value.");
		}
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
		$this->assertEquals(1, count($rows), "At this point there should be at least one entry.");
		//
		// Getting the last column of the single row and analyzing it's
		// buttons.
		$actionsColumn = $rows[0]->elements($this->using('css selector')->value('td'));
		$this->assertEquals(7 + 2, count($actionsColumn), "There are more/less columns than expected.");

		$actions = $actionsColumn[8]->elements($this->using('css selector')->value('a.btn'));
		$this->assertEquals(3, count($actions), "There are more/less action buttons in the last column than expected.");
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
			$this->assertEquals($conf['update'], $inputs[$field]->value(), "Value for field '{$field}' is not the one expected.");
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
		$this->assertRegExp("/\?action={$this->_pluralName}([#]?)$/", $this->url(), "Page didn't go back to the right place.");
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
		$this->assertEquals(7 + 2, count($headers), "There are more/less headers than expected.");
		//
		// Checking body.
		$tbody = $table->byTag('tbody');
		$this->assertTrue(boolval($tbody), "Table has no body.");

		$rows = $tbody->elements($this->using('css selector')->value('tr'));
		$this->assertEquals(0, count($rows), "At this point there shouldn't be any entry.");
	}
	// @}
	//
	// Table removal @{
	public function testRemovingTableUsingSystoolTable() {
		$cmd = "php shell.php sys table remove {$this->_singurlarName}";
		$cmd.= " --plural {$this->_pluralName}";
		$cmd.= " --module {$this->_moduleName}";
		$cmd.= ' --column name';
		$cmd.= ' --name-field name';
		$cmd.= ' --searchable person';
		$cmd.= ' --autocomplete';
		$cmd.= ' --type mysql';
		$this->runCommand($cmd);
		//
		// Checking that all expected assets where removed except those
		// that are generic.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			if(preg_match('~\.sqlite3~', $path)) {
				continue;
			}
			if(in_array($path, $this->_acceptableAssets)) {
				continue;
			}
			$this->assertNotTrue(is_file($path), "Asset '{$path}' was not removed.");
		}
	}
	// @}
}
