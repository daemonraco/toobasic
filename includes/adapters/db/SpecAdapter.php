<?php

/**
 * @file SpecAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

use TooBasic\Params;

/**
 * @class SpecAdapter
 * @abstract
 */
abstract class SpecAdapter extends \TooBasic\Adapters\Adapter {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\Adapters\DB\Adapter
	 */
	protected $_db = false;
	protected $_debugUpgrade = false;
	protected $_debugdbEmulation = false;
	protected $_engine = 'UnknownEngine';
	//
	// Magic methods.
	public function __construct(Adapter $db) {
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
	abstract public function compareIndex(\stdClass $index);
	abstract public function compareTable(\stdClass $table, &$creates, &$drops, &$updates);
	abstract public function createIndex(\stdClass $index);
	abstract public function createTable(\stdClass $table);
	abstract public function createTableColumn(\stdClass $table, $columnName);
	abstract public function dropIndex($indexName);
	abstract public function dropTable($tableName);
	abstract public function dropTableColumn(\stdClass $table, $columnName);
	public function executeCallback($callback) {
		$data = file_get_contents($callback[GC_AFIELD_PATH]);
		$replacements = [
			':tb_table_prefix:' => $this->_db->prefix()
		];
		foreach($replacements as $key => $replacement) {
			$data = preg_replace("/({$key})/", $replacement, $data);
		}
		$this->specificExecuteCallback($data);
	}
	abstract public function getIndexes();
	abstract public function getTables();
	abstract public function indexExists($indexName);
	public function keepUnknowns() {
		global $Connections;
		return $Connections[GC_CONNECTIONS_DEFAULTS][GC_CONNECTIONS_DEFAULTS_KEEPUNKNOWNS];
	}
	abstract public function tableExists($tableName);
	abstract public function updateIndex(\stdClass $index);
	abstract public function updateTableColumn(\stdClass $table, $columnName);
	//
	// Protected methods.
	final protected function debugUpgradeQuery($query) {
		if($this->_debugUpgrade) {
			\TooBasic\debugThing($query);
		}
	}
	protected function exec($query) {
		$this->debugUpgradeQuery($query);
		$out = $this->_debugdbEmulation ? true : $this->_db->exec($query, false) !== false;

		if(!$out) {
			$info = $this->_db->errorInfo();
			\TooBasic\debugThing("Unable to run query: {$query}. {$this->_engine} Error: [{$this->_db->errorCode()}] {$info[0]}-{$info[1]}-{$info[2]}", \TooBasic\DebugThingTypeError);
		}

		return $out;
	}
	abstract protected function specificExecuteCallback($data);
}
