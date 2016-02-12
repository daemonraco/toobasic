<?php

require_once __DIR__.'/XXXSystoolTableOn.php';

class Selenium_SystoolTableOnPostgreSQLTest extends Selenium_SystoolTableOnDatabaseTest {
	//
	// Inherited methods, weird but required @{
	public function testCreatingTableUsingSystoolTable() {
		parent::testCreatingTableUsingSystoolTable();
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
	public function testCheckingFirstEntryRemoval() {
		parent::testCheckingFirstEntryRemoval();
	}
	public function testCheckingEmptyListAgain() {
		parent::testCheckingEmptyListAgain();
	}
	public function testRemovingTableUsingSystoolTable() {
		parent::testRemovingTableUsingSystoolTable();
	}
	// @}
}
