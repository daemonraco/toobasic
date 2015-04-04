<?php

namespace TooBasic;

use \TooBasic\DBManager as TB_DBManager;

abstract class CacheAdapterDB extends CacheAdapter {
	//
	// Constants.
	//
	// Protected properties.
	protected $_compressionRate = 3;
	/**
	 * @var \TooBasic\DBAdapter
	 */
	protected $_db = false;
	/**
	 * @var string
	 */
	protected $_dbprefix = "";
	// 
	// Magic methods.
	public function __construct() {
		parent::__construct();
		global $Defaults;

		$this->_db = TB_DBManager::Instance()->getCache();
		$this->_dbprefix = $this->_db->prefix();

		if(!$Defaults[GC_DEFAULTS_INSTALLED]) {
			$this->checkTables();
		}
	}
	//
	// Public methods.
	public function delete($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key = :key \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			":key" => $this->fullKey($prefix, $key)
		));
	}
	public function get($prefix, $key) {
		$data = null;

		$this->cleanOld($prefix, $key);

		$query = "select  * \n";
		$query.= "from    {$this->_dbprefix}cache \n";
		$query.= "where	  cch_key = :key \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			":key" => $this->fullKey($prefix, $key)
		));

		if($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$data = unserialize(gzuncompress($row["cch_data"]));
		}

		return $data;
	}
	public function save($prefix, $key, $data) {
		$this->delete($prefix, $key);

		$query = "insert \n";
		$query.= "        into {$this->_dbprefix}cache ( \n";
		$query.= "                cch_key, cch_data) \n";
		$query.= "        values (:key, :data) \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			":key" => $this->fullKey($prefix, $key),
			":data" => gzcompress(serialize($data), $this->_compressionRate)
		));
	}
	//
	// Protected methods.
	abstract protected function checkTables();
	abstract protected function cleanOld($prefix, $key);
	protected function cleanPath($path, $forced = false) {
//		if(is_readable($path) && ($forced || (time() - filemtime($path)) >= 3600)) {
//			unlink($path);
//		}
	}
	protected function fullKey($prefix, $key, $genDir = false) {
		$key = sha1($key);
		$prefix.= ($prefix ? "_" : "");

		return "{$prefix}{$key}";
	}
}
