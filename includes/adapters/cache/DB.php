<?php

/**
 * @file DB.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\Cache;

//
// Class aliases.
use TooBasic\Managers\DBManager;
use TooBasic\Params;

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
	/**
	 * @var boolean When TRUE, all query execution errors are shown for debug.
	 */
	protected $_debugDBErrors = false;
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
		//
		// Loading debug flags.
		$this->_debugDBErrors = isset(Params::Instance()->debugdberrors);
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

		$stmtOk = $stmt->execute([
			':key' => $this->fullKey($prefix, $key)
		]);
		//
		// Debugging errors.
		if($this->_debugDBErrors && !$stmtOk) {
			debugit($stmt);
		}
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
	public function get($prefix, $key, $delay = self::EXPIRATION_SIZE_LARGE) {
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
		if($stmt->execute([':key' => $this->fullKey($prefix, $key)])) {
			$row = $stmt->fetch();
			if($row) {
				//
				// Decoding.
				$data = unserialize(gzuncompress($row['cch_data']));
			}
		} elseif($this->_debugDBErrors) {
			debugit($stmt);
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
	public function save($prefix, $key, $data, $delay = self::EXPIRATION_SIZE_LARGE) {
		//
		// Removing the previous value to avoid problems with the
		// insertion.
		$this->delete($prefix, $key);

		$query = "insert \n";
		$query.= "        into {$this->_dbprefix}cache ( \n";
		$query.= "                cch_key, cch_data) \n";
		$query.= "        values (:key, :data) \n";
		$stmt = $this->_db->prepare($query);
		//
		// Binding parameters. This msut be done in this way and not with
		// a simple array to avoid problems with BLOB fields.
		$fullKey = $this->fullKey($prefix, $key);
		$stmt->bindParam(':key', $fullKey, \PDO::PARAM_STR);
		$compressedData = gzcompress(serialize($data), $this->_compressionRate);
		$stmt->bindParam(':data', $compressedData, \PDO::PARAM_LOB);

		$stmtOk = $stmt->execute();
		//
		// Debugging errors.
		if($this->_debugDBErrors && !$stmtOk) {
			debugit($stmt);
		}
	}
	//
	// Protected methods.
	/**
	 * This method ensures the cache table existence.
	 */
	abstract protected function checkTables();
	/**
	 * This method removes expired cache entries.
	 *
	 * @param type $prefix Cache entry key prefix.
	 * @param type $key Cache entry key (without the prefix).
	 */
	abstract protected function cleanOld($prefix, $key);
	/**
	 * This method creates a proper cache entry key.
	 *
	 * @param string $prefix Key prefix of the entry to store.
	 * @param string $key Key of the entry to store.
	 * @return string Returns a normalize key.
	 */
	protected function fullKey($prefix, $key) {
		$key = sha1($key);
		$prefix.= ($prefix ? '_' : '');

		return "{$prefix}{$key}";
	}
}
