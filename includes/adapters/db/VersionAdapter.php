<?php

/**
 * @file VersionAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

//
// Class aliases.
use TooBasic\Adapters\Adapter;
use TooBasic\Managers\DBManager;
use TooBasic\Managers\DBStructureManager;

/**
 * @class VersionAdapter
 * @abstract
 * This class represent a basic adapter for database structure specification
 * version interpreters.
 */
abstract class VersionAdapter extends Adapter {
	// 
	// Protected class properties.
	/**
	 * @var string[] List of known and allowed column types.
	 */
	protected static $_AllowedColumnTypes = array(
		DBStructureManager::ColumnTypeBlob,
		DBStructureManager::ColumnTypeEnum,
		DBStructureManager::ColumnTypeFloat,
		DBStructureManager::ColumnTypeInt,
		DBStructureManager::ColumnTypeText,
		DBStructureManager::ColumnTypeTimestamp,
		DBStructureManager::ColumnTypeVarchar
	);
	/**
	 * @var string[] List of known column types that doesn't require a size
	 * specification.
	 */
	protected static $_ColumnTypesWithoutPrecisions = array(
		DBStructureManager::ColumnTypeBlob,
		DBStructureManager::ColumnTypeEnum,
		DBStructureManager::ColumnTypeText,
		DBStructureManager::ColumnTypeTimestamp,
	);
	//
	// Protected properties
	/**
	 * @var \TooBasic\Managers\DBManager Database manager shortcut.
	 */
	protected $_dbManager = false;
	/**
	 * @var \TooBasic\Managers\DBStructureManager Database structure manager
	 * shortcut.
	 */
	protected $_dbStructureManager = false;
	//
	// Magic methdos.
	/**
	 * Class constructor.
	 *
	 * @param \TooBasic\Managers\DBStructureManager $structureMgr Manager that
	 * created and instance of this class.
	 */
	public function __construct(DBStructureManager $structureMgr) {
		parent::__construct();
		//
		// Loading short cuts.
		$this->_dbManager = DBManager::Instance();
		$this->_dbStructureManager = $structureMgr;
	}
	//
	// Public methods.
	/**
	 * This method takes table specification read from configuration,
	 * validates it and convert it into a standard specification useful for
	 * the manager.
	 *
	 * @param \stdClass $table Table specification as it was loaded.
	 * @param mixed[string] $callbacks Currently knwon callbacks by the
	 * manager. This method will use it to merge it with its own list.
	 * @return mixed[string] Returns a list of values required by the manager
	 * to analyse and accept the parsing.
	 * @throws DBStructureManagerExeption
	 */
	abstract public function parseTable($table, $callbacks);
	//
	// Protected methods.
	/**
	 * This method generates a basic response for a table parsing method.
	 *
	 * @param \stdClass $table Table specification as it was loaded.
	 * @param mixed[string] $callbacks Currently knwon callbacks by the
	 * manager. This method will use it to merge it with its own list.
	 * @return mixed[string] Returns a list of values required by the manager
	 * to analyse and accept the parsing.
	 */
	public function parseTableStartResponse($table, $callbacks) {
		return array(
			GC_AFIELD_ERRORS => array(),
			GC_AFIELD_IGNORED => false,
			GC_AFIELD_CALLBACKS => $callbacks,
			GC_AFIELD_KEY => false,
			GC_AFIELD_SPECS => false,
			GC_AFIELD_INDEXES => array()
		);
	}
}
