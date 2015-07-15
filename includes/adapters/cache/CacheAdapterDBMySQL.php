<?php

namespace TooBasic;

class CacheAdapterDBMySQL extends CacheAdapterDB {
	//
	// Protected methods.
	protected function checkTables() {
		$query = "create table if not exists {$this->_dbprefix}cache ( \n";
		$query.= "        cch_key  varchar(256) collate utf8_bin not null, \n";
		$query.= "        cch_data blob not null, \n";
		$query.= "        cch_date timestamp not null default current_timestamp, \n";
		$query.= "        primary key(cch_key) \n";
		$query.= ") engine=myisam default charset=utf8 collate=utf8_bin \n";

		$this->_db->query($query);
	}
	protected function cleanOld($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key  = :key \n";
		$query.= " and        cch_date < date_sub(now(), interval :limit second)\n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			":key" => $this->fullKey($prefix, $key),
			":limit" => $this->_expirationLength
		));
	}
}
