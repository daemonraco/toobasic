<?php

require_once __DIR__.'/XXXSystoolTableOn.php';

class Selenium_SystoolTableOnMySQLV1Test extends Selenium_SystoolTableOnDatabaseTest {
	//
	// Inherited methods, weird but required @{
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
		$cmd.= ' --specs-version 1';
		$this->runCommand($cmd);
		//
		// Checking that all expected assets where generated.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			if(preg_match('~(\.sqlite3|testcases_curl\.cookies)~', $path)) {
				continue;
			}
			$this->assertTrue(is_file($path), "Asset '{$path}' was not generated.");
		}
	}
	public function testCheckingEmptyList() {
		parent::testCheckingEmptyList();
	}
	public function testCreatingANewEntryThroughTheForm() {
		parent::testCreatingANewEntryThroughTheForm();
	}
	public function testCheckingListWithTheFirstEntry() {
		parent::testCheckingListWithTheFirstEntry();
	}
	public function testCheckingFirstEntryInformation() {
		parent::testCheckingFirstEntryInformation();
	}
	public function testCheckingFirstEntryEdition() {
		parent::testCheckingFirstEntryEdition();
	}
	public function testUpdatingSearchIndex() {
		parent::testUpdatingSearchIndex();
	}
	public function testSearchingForTheEntryThroughServices() {
		parent::testSearchingForTheEntryThroughServices();
	}
	public function testSearchingForPartOfTheNameThroughServices() {
		parent::testSearchingForPartOfTheNameThroughServices();
	}
	public function testUsingThePredictiveSearchService() {
		parent::testUsingThePredictiveSearchService();
	}
	public function testCheckingFirstEntryRemoval() {
		parent::testCheckingFirstEntryRemoval();
	}
	public function testCheckingEmptyListAgain() {
		parent::testCheckingEmptyListAgain();
	}
	public function testRemovingTableUsingSystoolTable() {
		$cmd = "php shell.php sys table remove {$this->_singurlarName}";
		$cmd.= " --plural {$this->_pluralName}";
		$cmd.= " --module {$this->_moduleName}";
		$cmd.= ' --column name';
		$cmd.= ' --name-field name';
		$cmd.= ' --searchable person';
		$cmd.= ' --autocomplete';
		$cmd.= ' --type mysql';
		$cmd.= ' --specs-version 1';
		$this->runCommand($cmd);
		//
		// Checking that all expected assets where removed except those
		// that are generic.
		foreach(self::$_AssetsManager->generatedAssetFiles() as $path) {
			if(preg_match('~(\.sqlite3|testcases_curl\.cookies)~', $path)) {
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
