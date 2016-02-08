<?php

use TooBasic\Managers\DBManager;

/**
 * @class TestService
 *
 * Accessible at '?service=test'
 */
class TestService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = false;
	//
	// Protected methods.
	protected function basicRun() {
		$db = DBManager::Instance()->test;
		$stmt = $db->prepare('show databases');
		if($stmt->execute()) {
			$this->assign('executed', true);
			$this->assign('results', $stmt->fetchAll());
		} else {
			$this->assign('executed', false);
			$this->assign('results', array());
		}

		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
