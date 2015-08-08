<?php

/**
 * @file DBSQLite.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class DBSQLite
 */
class DBSQLite extends DB {
	//
	// Protected Properties.
	protected $_doesTableExist = null;
	//
	// Protected methods.
	/**
	 * This method ensures the cache table existence.
	 */
	protected function checkTables() {
		if(!$this->doesTableExist()) {
			$query = "create table {$this->_dbprefix}cache ( \n";
			$query.= "        cch_key  varchar(256) not null primary key, \n";
			$query.= "        cch_data blob not null, \n";
			$query.= "        cch_date timestamp not null default current_timestamp \n";
			$query.= ") \n";

			$this->_db->query($query);
		}
	}
	protected function cleanOld($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key  = :key \n";
		$query.= " and        cch_date < datetime('now', :limit) \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			':key' => $this->fullKey($prefix, $key),
			':limit' => "-{$this->_expirationLength} second"
		));
	}
	protected function doesTableExist() {
		if($this->_doesTableExist === null) {
			$pragma = $this->_db->queryData("pragma table_info({$this->_dbprefix}cache)");
			$this->_doesTableExist = count($pragma) > 0;
		}
		return $this->_doesTableExist;
	}
}
