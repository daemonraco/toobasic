<?php

/**
 * @file CacheAdapterDB.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use \TooBasic\DBManager as TB_DBManager;

/**
 * @class CacheAdapterDB
 * @abstract
 * This class represent a intermediate adapter for cache connection with entries
 * stored in database. Each of its specifications must provide the necessary code
 * to add, remove and retrieve entries from a database.
 */
abstract class CacheAdapterDB extends CacheAdapter {
	//
	// Protected properties.
	/**
	 * @var int Level of compression for GZip compressed data.
	 */
	protected $_compressionRate = 3;
	/**
	 * @var \TooBasic\DBAdapter Data base adapter pointer.
	 */
	protected $_db = false;
	/**
	 * @var string Tables prefix.
	 */
	protected $_dbprefix = '';
	// 
	// Magic methods.
	/**
	 * Class constructor.
	 */
	public function __construct() {
		parent::__construct();
		//
		// Global dependencies.
		global $Defaults;
		//
		// Loading database shortcuts.
		$this->_db = TB_DBManager::Instance()->getCache();
		$this->_dbprefix = $this->_db->prefix();
		//
		// If the system is not flagged as installed, the cache table
		// existence must be enforced.
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
			':key' => $this->fullKey($prefix, $key)
		));
	}
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
				$data = unserialize(gzuncompress($row['cch_data']));
			}
		}

		return $data;
	}
	public function save($prefix, $key, $data, $delay = self::ExpirationSizeLarge) {
		$this->delete($prefix, $key);

		$query = "insert \n";
		$query.= "        into {$this->_dbprefix}cache ( \n";
		$query.= "                cch_key, cch_data) \n";
		$query.= "        values (:key, :data) \n";
		$stmt = $this->_db->prepare($query);

		$stmt->bindParam(':key', $this->fullKey($prefix, $key), \PDO::PARAM_STR);
		$stmt->bindParam(':data', gzcompress(serialize($data), $this->_compressionRate), \PDO::PARAM_LOB);

		$stmt->execute();
	}
	//
	// Protected methods.
	abstract protected function checkTables();
	abstract protected function cleanOld($prefix, $key);
	protected function fullKey($prefix, $key) {
		$key = sha1($key);
		$prefix.= ($prefix ? '_' : '');

		return "{$prefix}{$key}";
	}
}
