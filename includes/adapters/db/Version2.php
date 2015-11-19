<?php

/**
 * @file Version2.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

use TooBasic\Managers\DBStructureManager;

/**
 * @class Version2
 */
class Version2 extends VersionAdapter {
	//
	// Constants.
	const PrecisionFloat = 11;
	const PrecisionInt = 11;
	const PrecisionVarchar = 256;
	//
	// Public methods.
	public function parseTable($table, $callbacks) {
		$out = $this->parseTableStartResponse($table, $callbacks);
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = array(
			GC_AFIELD_BEFORE_CREATE => array(),
			GC_AFIELD_AFTER_CREATE => array(),
			GC_AFIELD_BEFORE_DROP => array(),
			GC_AFIELD_AFTER_DROP => array(),
			GC_AFIELD_BEFORE_UPDATE => array(),
			GC_AFIELD_AFTER_UDPATE => array()
		);
		//
		// Of there are not fields, this specification is ignored.
		if(!$table->fields) {
			$out[GC_AFIELD_IGNORED] = true;
		} else {
			//
			// Default values.
			$tableIndexFileds = array(
				'keys' => array(),
				'primary' => array(),
				'indexes' => array()
			);
			$requiredTableFileds = array_merge(array(
				'name',
				'connection',
				'prefix',
				'engine',
				'callbacks',
				'version'
				), array_keys($tableIndexFileds));
			$tableFieldDefauls = array_merge(array(
				'version' => 2
				), $tableIndexFileds);
			$requiredFieldFileds = array(
				'type',
				'autoincrement',
				'null',
				'comment',
				'callbacks'
			);
			$fieldFieldDefauls = array(
				'autoincrement' => false,
				'null' => false,
				'keys' => array(),
				'primary' => array(),
				'indexes' => array()
			);
			//
			// Copying basic fields.
			$auxTable = \TooBasic\objectCopyAndEnforce($requiredTableFileds, $table, new \stdClass(), $tableFieldDefauls);
			//
			// Checking specification connection against
			// configuration.
			if(!isset($Connections[GC_CONNECTIONS_DB][$auxTable->connection])) {
				//
				// If there was a connection specified, an error
				// is shown.
				if($auxTable->connection) {
					$out[GC_AFIELD_ERRORS][] = array(
						GC_AFIELD_CODE => DBStructureManager::ErrorUnknownConnection,
						GC_AFIELD_MESSAGE => "Unknown connection named '{$auxTable->connection}'"
					);
				}
				//
				// Using default instalation connection.
				$auxTable->connection = $this->_dbManager->getInstallName();
			}
			//
			// Obtainig current connection table prefix.
			$prefix = '';
			if(isset($Connections[GC_CONNECTIONS_DB][$auxTable->connection][GC_CONNECTIONS_DB_PREFIX])) {
				$prefix = $Connections[GC_CONNECTIONS_DB][$auxTable->connection][GC_CONNECTIONS_DB_PREFIX];
			}
			//
			// Generating table's full name.
			$auxTable->fullname = "{$prefix}{$auxTable->name}";
			//
			// Generating a key to internally identify current table.
			$out[GC_AFIELD_KEY] = sha1("{$auxTable->connection}-{$auxTable->name}");
			//
			// Loading table fields @{
			$auxTable->fields = array();
			foreach($table->fields as $field => $spec) {
				//
				// Transforming simplest specs into a basic
				// object.
				if(!is_object($spec)) {
					$auxSpec = new \stdClass();
					$auxSpec->type = $spec;
					$spec = $auxSpec;
				}
				//
				// Copying basic fields.
				$auxField = \TooBasic\objectCopyAndEnforce($requiredFieldFileds, $spec, new \stdClass(), $fieldFieldDefauls);
				//
				// Generating fullname.
				$auxField->fullname = "{$auxTable->prefix}{$field}";
				//
				// Expanding type @{
				$auxField->type = $this->expandType($auxField->type, $out[GC_AFIELD_ERRORS]);
				if($auxField->type === false) {
					continue;
				}
				// @}
				//
				// Analysing default value settings.
				if(isset($spec->default)) {
					$auxField->default = $spec->default;
					$auxField->hasDefault = true;
				} else {
					$auxField->hasDefault = false;
				}
				//
				// Field callbacks.
				$auxField->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $auxField->callbacks instanceof \stdClass ? $auxField->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
				//
				// Parsing and expanding column callbacks list.
				foreach(array_keys($callbackEntries) as $callbackType) {
					if(!isset($auxField->callbacks->{$callbackType})) {
						$auxField->callbacks->{$callbackType} = array();
					} elseif(!is_array($auxField->callbacks->{$callbackType})) {
						$auxField->callbacks->{$callbackType} = array(
							$auxField->callbacks->{$callbackType}
						);
					}
					foreach($auxField->callbacks->{$callbackType} as &$call) {
						$callbackKey = "F_{$callbackType}_{$out[GC_AFIELD_KEY]}_{$auxField->fullname}";
						if(!isset($out[GC_AFIELD_CALLBACKS][$callbackKey])) {
							$out[GC_AFIELD_CALLBACKS][$callbackKey] = array();
						}
						$out[GC_AFIELD_CALLBACKS][$callbackKey][] = array(
							GC_AFIELD_NAME => $call
						);

						$call = $callbackKey;
					}
				}
				//
				// Accepting field specs.
				$auxTable->fields[$auxField->fullname] = $auxField;
			}
			// @}
			//
			// If there are no fields for this table it is ignored.
			if(!$auxTable->fields) {
				$out[GC_AFIELD_IGNORED] = true;
			} else {
				//
				// Table callbacks.
				$auxTable->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $auxTable->callbacks instanceof \stdClass ? $auxTable->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
				//
				// Parsing and expanding table callbacks list.
				foreach(array_keys($callbackEntries) as $callbackType) {
					if(!isset($auxTable->callbacks->{$callbackType})) {
						$auxTable->callbacks->{$callbackType} = array();
					} elseif(!is_array($auxTable->callbacks->{$callbackType})) {
						$auxTable->callbacks->{$callbackType} = array(
							$auxTable->callbacks->{$callbackType}
						);
					}

					foreach($auxTable->callbacks->{$callbackType} as &$call) {
						$callbackKey = "T_{$callbackType}_{$out[GC_AFIELD_KEY]}";
						if(!isset($out[GC_AFIELD_CALLBACKS][$callbackKey])) {
							$out[GC_AFIELD_CALLBACKS][$callbackKey] = array();
						}
						$out[GC_AFIELD_CALLBACKS][$callbackKey][] = array(
							GC_AFIELD_NAME => $call
						);

						$call = $callbackKey;
					}
				}

				$out[GC_AFIELD_SPECS] = $auxTable;
			}
			//
			// Checking index definitions.
			if(!$out[GC_AFIELD_IGNORED] && count($out[GC_AFIELD_ERRORS]) == 0) {
				foreach(array_keys($tableIndexFileds) as $indexType) {
					foreach($out[GC_AFIELD_SPECS]->{$indexType} as $index => $fields) {
						if($fields) {
							$auxIndex = new \stdClass();
							$auxIndex->name = "{$auxTable->prefix}{$index}";
							$auxIndex->table = $out[GC_AFIELD_SPECS]->name;
							switch($indexType) {
								case 'primary':
									$auxIndex->type = 'primary';
									break;
								case 'keys':
									$auxIndex->type = 'key';
									break;
								case 'indexes':
								default:
									$auxIndex->type = 'index';
							}
							$auxIndex->connection = $out[GC_AFIELD_SPECS]->connection;
							$auxIndex->fields = $fields;
							$auxIndex->callbacks = array(
								GC_AFIELD_BEFORE_CREATE => array(),
								GC_AFIELD_AFTER_CREATE => array(),
								GC_AFIELD_BEFORE_DROP => array(),
								GC_AFIELD_AFTER_DROP => array()
							);

							$out[GC_AFIELD_INDEXES][] = $auxIndex;
						}
						//
						// Removing index V2 specs to
						// avoid confusion.
						unset($out[GC_AFIELD_SPECS]->{$indexType});
					}
				}
			}
		}

		return $out;
	}
	//
	// Protected methods.
	protected function expandType($type, &$errors) {
		//
		// Default values.
		$out = false;
		//
		// Expanding type specification.
		$expType = explode(':', $type);
		//
		// Checking if it's an allowed type.
		if(in_array($expType[0], self::$_AllowedColumnTypes)) {
			$out = new \stdClass();
			switch($expType[0]) {
				case DBStructureManager::ColumnTypeInt:
					$out->type = DBStructureManager::ColumnTypeInt;
					$out->precision = isset($expType[1]) ? $expType[1] : self::PrecisionInt;
					break;
				case DBStructureManager::ColumnTypeVarchar:
					$out->type = DBStructureManager::ColumnTypeVarchar;
					$out->precision = isset($expType[1]) ? $expType[1] : self::PrecisionVarchar;
					break;
				case DBStructureManager::ColumnTypeEnum:
					if(isset($expType[1])) {
						//
						// Ignoring first value becuase it
						// is the actual type.
						array_shift($expType);
						$this->values = array();
						foreach($expType as $v) {
							$this->values[] = $v;
						}
					} else {
						$out[GC_AFIELD_ERRORS][] = array(
							GC_AFIELD_CODE => DBStructureManager::ErrorDefault,
							GC_AFIELD_MESSAGE => "Field '{$auxField->fullname}' of table '{$aux->name}' is enumerative and has no value"
						);
					}
					break;
				case DBStructureManager::ColumnTypeFloat:
					$out->type = DBStructureManager::ColumnTypeFloat;
					$out->precision = isset($expType[1]) ? $expType[1] : self::PrecisionFloat;
					break;
				case DBStructureManager::ColumnTypeBlob:
				case DBStructureManager::ColumnTypeText:
				case DBStructureManager::ColumnTypeTimestamp:
					$out->type = $expType[0];
					$out->precision = false;
					break;
				default:
					$errors[] = array(
						GC_AFIELD_CODE => DBStructureManager::ErrorUnknownType,
						GC_AFIELD_MESSAGE => "Unhandle type '{$expType[0]}'"
					);
					$out = false;
			}
		} else {
			$errors[] = array(
				GC_AFIELD_CODE => DBStructureManager::ErrorUnknownType,
				GC_AFIELD_MESSAGE => "Type '{$expType[0]}' is not allowed"
			);
		}

		return $out;
	}
}
