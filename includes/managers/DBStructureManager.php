<?php

namespace TooBasic;

class DBStructureManager extends Manager {
	//
	// Constants.
	const ErrorOk = 0;
	const ErrorDefault = 1;
	const ErrorUnknownTable = 2;
	const ErrorUnknownType = 3;
	const ErrorUnknownConnection = 4;
	const ColumnTypeBlob = "blob";
	const ColumnTypeFloat = "float";
	const ColumnTypeInt = "int";
	const ColumnTypeText = "text";
	const ColumnTypeVarchar = "varchar";
	// 
	// Protected class properties.
	protected static $_AllowedColumnTypes = array(
		self::ColumnTypeBlob,
		self::ColumnTypeFloat,
		self::ColumnTypeInt,
		self::ColumnTypeText,
		self::ColumnTypeVarchar
	);
	// 
	// Protected properties.
	protected $_errors = array();
	protected $_installed = false;
	protected $_specFiles = array();
	protected $_specs = false;
	//
	// Public methods.
	public function check() {
		$ok = false;

		if(!$this->_installed) {

			$ok = $this->hasErrors();
		} else {
			$ok = true;
		}

		return $ok;
	}
	public function errors() {
		return $this->_errors;
	}
	public function hasErrors() {
		return boolval($this->_errors);
	}
	public function upgrade() {
		debugit("@TODO", true);
	}
	//
	// Protected methods.
	protected function checkSpecs() {
		//
		// Checking indexes.
		foreach($this->_specs->indexes as $iKey => $index) {
			$tKey = sha1("{$index->connection}-{$index->table}");
			if(!isset($this->_specs->tables[$tKey])) {
				$this->setError(self::ErrorUnknownTable, "Index '{$index->fullname}' uses an unknown table called '{$index->table}'");
				unset($this->_specs->indexes[$iKey]);
				continue;
			}
		}
	}
	protected function init() {
		parent::init();

		global $Defaults;
		$this->_installed = $Defaults[GC_DEFAULTS_INSTALLED];
		//
		// If the system is not set as installed, there are other
		// conditions that may stop this manager from cheking and
		// installing, these are:
		//	- no database connections.
		//	- no default database connection.
		//	- wrong default database connection.
		if(!$this->_installed) {
			global $Connections;
			//
			// No database connections.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB]) || !boolval($Connections[GC_CONNECTIONS_DB]))) {
				$this->_installed = true;
			}
			//
			// No default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]) || !$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB])) {
				$this->_installed = true;
			}
			// 
			// Wrong default database connection.
			if(!$this->_installed && (!isset($Connections[GC_CONNECTIONS_DB][$Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB]]))) {
				$this->_installed = true;
			}
		}
		//
		// There's a way to force installation checks and upgrades, but
		// only from shell tools.
		if(defined("__SHELL__") && isset(Params::Instance()->debugforcedbinstall)) {
			$this->_installed = false;
		}
		//
		// If the system is not flagged as installed, it must load the
		// database specification and check it.
		if(!$this->_installed) {
			$this->loadSpecs();
			$this->parseSpecs();
			$this->checkSpecs();

			if(isset(Params::Instance()->debugdbstructure)) {
				debugit(array(
					"errors" => $this->_errors,
					"files" => $this->_specFiles,
					"specs" => $this->_specs
				));
			}
		}
	}
	protected function loadSpecs() {
		//
		// Adding configuration.
		$this->_specFiles = Paths::Instance()->dbSpecPaths();
		//
		// Adding default configuration.
		global $Database;
		array_unshift($this->_specFiles, $Database[GC_DATABASE_DEFAULT_SPECS]);
	}
	protected function parseSpec($path) {
		if(!$this->_specs) {
			$this->_specs = new \stdClass();
		}

		$json = json_decode(file_get_contents($path));

		$this->parseSpecConfigs(isset($json->configs) ? $json->configs : new \stdClass());
		$this->parseSpecTables(isset($json->tables) ? $json->tables : new \stdClass());
		$this->parseSpecIndexes(isset($json->indexes) ? $json->indexes : new \stdClass());
	}
	protected function parseSpecConfigs($configs) {
		//
		// Enforcing given config @{
		if(!isset($configs->prefixes)) {
			$configs->prefixes = new \stdClass();
		}
		// @}
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->configs)) {
			$this->_specs->configs = new \stdClass();
		}
		//
		// Required configs' fileds.
		foreach(array("prefixes") as $field) {
			if(!isset($this->_specs->configs->{$field})) {
				$this->_specs->configs->{$field} = new \stdClass();
			}
		}
		//
		// Loading prefixes.
		$this->_specs->configs->prefixes = self::CopyAndEnforce(array("index", "key", "primary"), $configs->prefixes, $this->_specs->configs->prefixes);
	}
	protected function parseSpecIndexes($indexes) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->indexes)) {
			$this->_specs->indexes = array();
		}

		global $Connections;

		foreach($indexes as $index) {
			$aux = self::CopyAndEnforce(array("name", "table", "type", "connection"), $index, new \stdClass());

			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
			}

			$prefix = "";
			if(isset($this->_specs->configs->prefixes->{$aux->type})) {
				$prefix = $this->_specs->configs->prefixes->{$aux->type};
			}
			$aux->fullname = "{$prefix}{$aux->name}";

			$this->_specs->indexes[sha1("{$aux->connection}-{$aux->fullname}")] = $aux;
		}
	}
	protected function parseSpecs() {
		foreach($this->_specFiles as $path) {
			$this->parseSpec($path);
		}
	}
	protected function parseSpecTables($tables) {
		//
		// Creating main object when it's not there.
		if(!isset($this->_specs->tables)) {
			$this->_specs->tables = array();
		}

		global $Connections;

		foreach($tables as $table) {
			if(!$table->fields) {
				continue;
			}

			$aux = self::CopyAndEnforce(array("name", "connection", "prefix"), $table, new \stdClass());

			$aux->fields = array();
			foreach($table->fields as $field) {
				$auxField = self::CopyAndEnforce(array("name", "type", "autoincrement"), $field, new \stdClass());
				$auxField->fullname = "{$aux->prefix}{$auxField->name}";

				if(!isset($auxField->type->type)) {
					$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' has no type");
					continue;
				} elseif(!in_array($auxField->type->type, self::$_AllowedColumnTypes)) {
					$this->setError(self::ErrorUnknownType, "Unknown field type '{$auxField->type->type}' for field '{$auxField->fullname}' on table '{$aux->name}'");
					continue;
				}
				if(!isset($auxField->type->precision)) {
					$this->setError(self::ErrorDefault, "Field '{$auxField->fullname}' of table '{$aux->name}' has no precision");
					continue;
				}

				$aux->fields[$auxField->fullname] = $auxField;
			}

			if(!$aux->fields) {
				continue;
			}
			if(!isset($Connections[GC_CONNECTIONS_DB][$aux->connection])) {
				if($aux->connection) {
					$this->setError(self::ErrorUnknownConnection, "Unknown connection named '{$aux->connection}'");
				}
				$aux->connection = $Connections[GC_CONNECTIONS_DEFAUTLS][GC_CONNECTIONS_DEFAUTLS_DB];
			}

			$prefix = "";
			if(isset($Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX])) {
				$prefix = $Connections[GC_CONNECTIONS_DB][$aux->connection][GC_CONNECTIONS_DB_PREFIX];
			}
			$aux->fullname = "{$prefix}{$aux->name}";

			$key = sha1("{$aux->connection}-{$aux->name}");
			if(!isset($this->_specs->tables[$key])) {
				$this->_specs->tables[$key] = $aux;
			} else {
				if($this->_specs->tables[$key]->prefix == $aux->prefix) {
					foreach($aux->fields as $field) {
						$this->_specs->tables[$key]->fields[$field->fullname] = $field;
					}
				}
			}
		}
	}
	protected function setError($code, $message) {
		$this->_errors[] = array(
			"code" => $code,
			"message" => $message
		);
	}
	//
	// Protected class methods.
	protected static function CopyAndEnforce($fields, \stdClass $origin, \stdClass $destination, $defualt = "") {
		foreach($fields as $field) {
			if(isset($origin->{$field})) {
				$destination->{$field} = $origin->{$field};
			} elseif(!isset($destination->{$field})) {
				$destination->{$field} = $defualt;
			}
		}

		return $destination;
	}
}
