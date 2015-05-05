<?php

namespace TooBasic;

abstract class DBSpecAdapter extends Adapter {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\DBAdapter
	 */
	protected $_db = false;
	protected $_debugUpgrade = false;
	protected $_debugdbEmulation = false;
	//
	// Magic methods.
	public function __construct(DBAdapter $db) {
		parent::__construct();

		$this->_db = $db;
		$this->_debugUpgrade = isset(Params::Instance()->debugdbupgrade);
		$this->_debugdbEmulation = isset(Params::Instance()->debugdbemulation);
		$this->_debugUpgrade = $this->_debugdbEmulation ? true : $this->_debugUpgrade;
	}
	//
	// Public methods.
	abstract public function addTableEntry(\stdClass $table, \stdClass $entry);
	abstract public function checkTableEntry(\stdClass $table, \stdClass $entry);
	abstract public function compareTable(\stdClass $table, &$creates, &$drops, &$updates);
	abstract public function createIndex(\stdClass $index);
	abstract public function createTable(\stdClass $table);
	abstract public function createTableColumn(\stdClass $table, $columnName);
	abstract public function dropIndex($indexName);
	abstract public function dropTable($tableName);
	abstract public function dropTableColumn(\stdClass $table, $columnName);
	abstract public function getIndexes();
	abstract public function getTables();
	abstract public function indexExists($indexName);
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_KEEPUNKNOWNS];
	}
	abstract public function tableExists($tableName);
	abstract public function updateTableColumn(\stdClass $table, $columnName);
	//
	// Protected methods.
	final public function debugUpgradeQuery($query) {
		if($this->_debugUpgrade) {
			debugit($query);
		}
	}
	public function exec($query) {
		$this->debugUpgradeQuery($query);
		return $this->_debugdbEmulation ? "true" : $this->_db->exec($query, false) !== false;
	}
}
