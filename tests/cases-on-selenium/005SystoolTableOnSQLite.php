<?php

require_once __DIR__.'/XXXSystoolTableOn.php';

class Selenium_SystoolTableOnSQLiteTest extends Selenium_SystoolTableOnDatabaseTest {
	//
	// Inherited methods, weird but required @{
	public function testCreatingTableUsingSystoolTable() {
		parent::testCreatingTableUsingSystoolTable();
		$this->runCommand('chmod 0666 '.TESTS_ROOTDIR.'/cache/travis_test.sqlite3');
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
		$this->_acceptableAssets[] = TESTS_ROOTDIR."/cache/travis_test.sqlite3";
		parent::testRemovingTableUsingSystoolTable();
	}
	// @}
}
