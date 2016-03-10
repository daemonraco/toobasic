<?php

use TooBasic\Managers\DBManager;

class EntriesModel extends \TooBasic\Model {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter Database connection shortcut
	 */
	protected $_db = false;
	/**
	 * @var string Database connection name shortcut.
	 */
	protected $_dbprefix = '';
	//
	// Public methods.
	public function getChildren() {
		$out = [];

		$conditions = [];

		$prefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'chl_'
		);

		$query = $this->_db->queryAdapter()->select('children', $conditions, $prefixes);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Retrieving information.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$out = $stmt->fetchAll();
		} else {
			debugit([
				'$query' => $query,
				error => $stmt->errorInfo()
			]);
			throw new \TooBasic\DBException('Database exeption');
		}

		return $out;
	}
	public function getEntries($search = false) {
		$out = [];

		$conditions = [
			'C:chl_parent' => 'mas_id'
		];

		if($search !== false) {
			$conditions['*:chl_name'] = $search;
		}

		$prefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix
		);

		$query = $this->_db->queryAdapter()->select(array(
			'master',
			'children'
			), $conditions, $prefixes, array(
			'mas_id' => 'ASC'
			), 10);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Retrieving information.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$out = $stmt->fetchAll();
		} else {
			debugit([
				'$query' => $query,
				error => $stmt->errorInfo()
			]);
			throw new \TooBasic\DBException('Database exeption');
		}

		return $out;
	}
	public function getMasters() {
		$out = [];

		$conditions = [];

		$prefixes = array(
			GC_DBQUERY_PREFIX_TABLE => $this->_dbprefix,
			GC_DBQUERY_PREFIX_COLUMN => 'mas_'
		);

		$query = $this->_db->queryAdapter()->select('master', $conditions, $prefixes);
		$stmt = $this->_db->prepare($query[GC_AFIELD_QUERY]);
		//
		// Retrieving information.
		if($stmt->execute($query[GC_AFIELD_PARAMS])) {
			$out = $stmt->fetchAll();
		} else {
			debugit([
				'$query' => $query,
				error => $stmt->errorInfo()
			]);
			throw new \TooBasic\DBException('Database exeption');
		}

		return $out;
	}
	//
	// Protected methods.
	protected function init() {
		//
		// Generating shortcuts.
		$this->_db = DBManager::Instance()->getDefault();
		$this->_dbprefix = $this->_db->prefix();
	}
}
