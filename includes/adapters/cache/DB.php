<?php

/**
 * @file DB.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

//
// Class aliases.
use TooBasic\Managers\DBManager;

/**
 * @class DB
 * @abstract
 * This class represent a intermediate adapter for cache connection with entries
 * stored in database. Each of its specifications must provide the necessary code
 * to add, remove and retrieve entries from a database.
 */
abstract class DB extends Adapter {
	//
	// Protected properties.
	/**
	 * @var int Level of compression for GZip compressed data.
	 */
	protected $_compressionRate = 3;
	/**
	 * @var \TooBasic\Adapters\DB\Adapter Data base adapter pointer.
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
		$this->_db = DBManager::Instance()->getCache();
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
	/**
	 * This method removes a cache entry.
	 *
	 * @param string $prefix Key prefix of the entry to remove.
	 * @param string $key Key of the entry to remove.
	 */
	public function delete($prefix, $key) {
		$query = "delete from {$this->_dbprefix}cache \n";
		$query.= "where       cch_key = :key \n";
		$stmt = $this->_db->prepare($query);

		$stmt->execute(array(
			':key' => $this->fullKey($prefix, $key)
		));
	}
	/**
	 * This method retieves a cache entry data.
	 *
	 * @param string $prefix Key prefix of the entry to retieve.
	 * @param string $key Key of the entry to retieve.
	 * @param int $delay Amount of seconds the entry lasts.
	 * @return mixed Return the infomation stored in the request cache entry
	 * or NULL if none found.
	 */
	public function get($prefix, $key, $delay = self::ExpirationSizeLarge) {
		//
		// Default values.
		$data = null;
		//
		// Cleaning the entry in case it is too old.
		$this->cleanOld($prefix, $key);
		//
		// Preparing the query to obtain an entry.
		$query = "select  * \n";
		$query.= "from    {$this->_dbprefix}cache \n";
		$query.= "where	  cch_key = :key \n";
		$stmt = $this->_db->prepare($query);
		//
		// Requesting a specific entry.
		if($stmt->execute(array(':key' => $this->fullKey($prefix, $key)))) {
			$row = $stmt->fetch();
			if($row) {
				//
				// Decoding.
				$data = unserialize(gzuncompress($row['cch_data']));
			}
		}

		return $data;
	}
	/**
	 * This method stores information in cache and associates it to a certain
	 * cache key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @param mixed $data Information to store.
	 * @param int $delay Amount of seconds the entry lasts.
	 */
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
