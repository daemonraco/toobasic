<?php

/**
 * @file VersionAdapter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

use TooBasic\Managers\DBManager;
use TooBasic\Managers\DBStructureManager;

/**
 * @class VersionAdapter
 * @abstract
 */
abstract class VersionAdapter extends \TooBasic\Adapters\Adapter {
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
	protected $_dbManager = false;
	protected $_dbStructureManager = false;
	//
	// Magic methdos.
	public function __construct(\TooBasic\Managers\DBStructureManager $structureMgr) {
		parent::__construct();

		$this->_dbManager = DBManager::Instance();
		$this->_dbStructureManager = $structureMgr;
	}
	//
	// Public methods.
	abstract public function parseTable($table, $callbacks);
	//
	// Protected methods.
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
