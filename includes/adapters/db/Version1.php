<?php

/**
 * @file Version1.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Adapters\DB;

//
// Class aliases.
use TooBasic\Managers\DBStructureManager;

/**
 * @class Version1
 * This interpreter holds the logic to manage version 1 of database structure
 * specifications.
 */
class Version1 extends VersionAdapter {
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
	public function parseTable($table, $callbacks) {
		$out = $this->parseTableStartResponse($table, $callbacks);
		//
		// Global dependencies.
		global $Connections;
		//
		// Basic callback entries:
		$callbackEntries = [
			GC_AFIELD_BEFORE_CREATE => [],
			GC_AFIELD_AFTER_CREATE => [],
			GC_AFIELD_BEFORE_DROP => [],
			GC_AFIELD_AFTER_DROP => [],
			GC_AFIELD_BEFORE_UPDATE => [],
			GC_AFIELD_AFTER_UDPATE => []
		];
		//
		// Of there are not fields, this specification is ignored.
		if(!$table->fields) {
			$out[GC_AFIELD_IGNORED] = true;
		} else {
			//
			// Copying basic fields.
			$aux = \TooBasic\objectCopyAndEnforce(['name', 'connection', 'prefix', 'engine', 'callbacks', 'version'], $table, new \stdClass(), ['version' => 1]);
			//
			// Checking specification connection against
			// configuration.
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				// 
				// If there was a connection specified, an error
				// is shown.
				if($aux->connection) {
					$out[GC_AFIELD_ERRORS][] = [
						GC_AFIELD_CODE => DBStructureManager::ERROR_UNKNOWN_CONNECTION,
						GC_AFIELD_MESSAGE => "Unknown connection named '{$aux->connection}'"
					];
				}
				//
				// Using default instalation connection.
				$aux->connection = $this->_dbManager->getInstallName();
			}
			//
			// Obtainig current connection table prefix.
			$prefix = '';
			if(isset($Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX])) {
				$prefix = $Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX];
			}
			//
			// Generating table's full name.
			$aux->fullname = "{$prefix}{$aux->name}";
			//
			// Generating a key to internally identify current table.
			$out[GC_AFIELD_KEY] = sha1("{$aux->connection}-{$aux->name}");
			//
			// Loading table fields @{
			$aux->fields = [];
			foreach($table->fields as $field) {
				//
				// Copying basic fields.
				$auxField = \TooBasic\objectCopyAndEnforce(['name', 'type', 'autoincrement', 'null', 'comment', 'callbacks'], $field, new \stdClass(), ['autoincrement' => false, 'null' => false]);
				//
				// Generating fullname.
				$auxField->fullname = "{$aux->prefix}{$auxField->name}";
				//
				// If theres no type's type for this field and
				// error is set and it's ignored.
				// Also if the type's type is unknown.
				if(!isset($auxField->type->type)) {
					$out[GC_AFIELD_ERRORS][] = [
						GC_AFIELD_CODE => DBStructureManager::ERROR_DEFAULT,
						GC_AFIELD_MESSAGE => "Field '{$auxField->fullname}' of table '{$aux->name}' has no type"
					];
					continue;
				} elseif(!in_array($auxField->type->type, self::$_AllowedColumnTypes)) {
					$out[GC_AFIELD_ERRORS][] = [
						GC_AFIELD_CODE => DBStructureManager::ERROR_UNKNOWN_TYPE,
						GC_AFIELD_MESSAGE => "Unknown field type '{$auxField->type->type}' for field '{$auxField->fullname}' on table '{$aux->name}'"
					];
					continue;
				}
				//
				// Analysing column's precision.
				/** @todo check this, why there's no 'else'? */
				if(!isset($auxField->type->precision) || !$auxField->type->precision) {
					if($auxField->type->type == DBStructureManager::COLUMN_TYPE_ENUM && !isset($auxField->type->values)) {
						$out[GC_AFIELD_ERRORS][] = [
							GC_AFIELD_CODE => DBStructureManager::ERROR_DEFAULT,
							GC_AFIELD_MESSAGE => "Field '{$auxField->fullname}' of table '{$aux->name}' is enumerative and has no value"
						];
						continue;
					} elseif(!in_array($auxField->type->type, self::$_ColumnTypesWithoutPrecisions)) {
						$out[GC_AFIELD_ERRORS][] = [
							GC_AFIELD_CODE => DBStructureManager::ERROR_DEFAULT,
							GC_AFIELD_MESSAGE => "Field '{$auxField->fullname}' of table '{$aux->name}' has no precision"
						];
						continue;
					}
				}
				//
				// Analysing default value settings.
				if(isset($field->default)) {
					$auxField->default = $field->default;
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
						$auxField->callbacks->{$callbackType} = [];
					} elseif(!is_array($auxField->callbacks->{$callbackType})) {
						$auxField->callbacks->{$callbackType} = [
							$auxField->callbacks->{$callbackType}
						];
					}
					foreach($auxField->callbacks->{$callbackType} as &$call) {
						$callbackKey = "F_{$callbackType}_{$out[GC_AFIELD_KEY]}_{$auxField->fullname}";
						if(!isset($out[GC_AFIELD_CALLBACKS][$callbackKey])) {
							$out[GC_AFIELD_CALLBACKS][$callbackKey] = [];
						}
						$out[GC_AFIELD_CALLBACKS][$callbackKey][] = [
							GC_AFIELD_NAME => $call
						];

						$call = $callbackKey;
					}
				}
				//
				// Accepting field specs.
				$aux->fields[$auxField->fullname] = $auxField;
			}
			// @}
			//
			// If there are no fields for this table it is ignored.
			if(!$aux->fields) {
				$out[GC_AFIELD_IGNORED] = true;
			} else {
				//
				// Table callbacks.
				$aux->callbacks = \TooBasic\objectCopyAndEnforce(array_keys($callbackEntries), $aux->callbacks instanceof \stdClass ? $aux->callbacks : new \stdClass(), new \stdClass(), $callbackEntries);
				//
				// Parsing and expanding table callbacks list.
				foreach(array_keys($callbackEntries) as $callbackType) {
					if(!isset($aux->callbacks->{$callbackType})) {
						$aux->callbacks->{$callbackType} = [];
					} elseif(!is_array($aux->callbacks->{$callbackType})) {
						$aux->callbacks->{$callbackType} = [
							$aux->callbacks->{$callbackType}
						];
					}

					foreach($aux->callbacks->{$callbackType} as &$call) {
						$callbackKey = "T_{$callbackType}_{$out[GC_AFIELD_KEY]}";
						if(!isset($out[GC_AFIELD_CALLBACKS][$callbackKey])) {
							$out[GC_AFIELD_CALLBACKS][$callbackKey] = [];
						}
						$out[GC_AFIELD_CALLBACKS][$callbackKey][] = [
							GC_AFIELD_NAME => $call
						];

						$call = $callbackKey;
					}
				}

				$out[GC_AFIELD_SPECS] = $aux;
			}
		}

		return $out;
	}
}
