<?php

/**
 * @file DBSQLite.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class DBSQLite
 * This class provides and cache adaptation for entries stored on SQLite
 * databeses.
 */
class DBSQLite extends DB {
	//
	// Protected Properties.
	/**
	 * @var bool Indicates if the table for cache entries storage exists. NULL
	 * means it was not checked yet.
	 */
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
	/**
	 * This method removes expired cache entries.
	 *
	 * @param type $prefix Cache entry key prefix.
	 * @param type $key Cache entry key (without the prefix).
	 */
	protected function cleanOld($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key  = :key \n";
		$query.= " and        cch_date < datetime('now', :limit) \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute([
			':key' => $this->fullKey($prefix, $key),
			':limit' => "-{$this->_expirationLength} second"
		]);
	}
	/**
	 * Checks if the table for cache entries storage exists.
	 *
	 * @return bool TRUE when the table exists.
	 */
	protected function doesTableExist() {
		if($this->_doesTableExist === null) {
			$pragma = $this->_db->queryData("pragma table_info({$this->_dbprefix}cache)");
			$this->_doesTableExist = count($pragma) > 0;
		}
		return $this->_doesTableExist;
	}
}
