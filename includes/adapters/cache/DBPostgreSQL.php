<?php

/**
 * @file DBPostgreSQL.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

/**
 * @class DBPostgreSQL
 */
class DBPostgreSQL extends DB {
	//
	// Protected Properties.
	protected $_doesTableExist = null;
	//
	// Public methods.
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
		$data = null;

		$this->cleanOld($prefix, $key);

		$query = "select  * \n";
		$query.= "from    {$this->_dbprefix}cache \n";
		$query.= "where	  cch_key = :key \n";
		$stmt = $this->_db->prepare($query);

		if($stmt->execute(array(':key' => $this->fullKey($prefix, $key)))) {
			$row = $stmt->fetch();
			if($row) {
				$fdata = '';
				while(!feof($row['cch_data'])) {
					$fdata.= fgets($row['cch_data']);
				}
				$data = unserialize(gzuncompress($fdata));
			}
		}

		return $data;
	}
	//
	// Protected methods.
	protected function doesTableExist() {
		if($this->_doesTableExist === null) {
			$this->_doesTableExist = count($this->_db->queryData("select * from pg_class where relname = '{$this->_dbprefix}cache'")) > 0;
		}
		return $this->_doesTableExist;
	}
	protected function checkTables() {
		if(!$this->doesTableExist()) {
			$query = "create table {$this->_dbprefix}cache ( \n";
			$query.= "        cch_key  varchar(256) not null primary key, \n";
			$query.= "        cch_data bytea not null, \n";
			$query.= "        cch_date timestamp not null default current_timestamp \n";
			$query.= ") \n";

			$this->_db->query($query);
		}
	}
	protected function cleanOld($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key  = :key \n";
		$query.= " and        cch_date < now() - interval '{$this->_expirationLength} second' \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			':key' => $this->fullKey($prefix, $key)
		));
	}
}
