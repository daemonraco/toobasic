<?php

/**
 * @file ItemRepresentation.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Representations;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Names;
use TooBasic\Paths;
use TooBasic\Translate;

/**
 * @class CoreProps
 * @abstract
 * This class represent a holder of core properties of table representations and
 * its used by item's representations and items' factories.
 */
abstract class CoreProps {
	//
	// Protected class properties.
	protected static $_LoadedInstances = [];
	//
	// Protected core properties.
	/**
	 * @var string Generic prefix for all columns on the represented table.
	 */
	protected $_ColumnsPerfix = '';
	/**
	 * @var string[string] Associative list of field names and the filter to
	 * be applied on them.
	 */
	protected $_ColumnFilters = [];
	/**
	 * @var boolean This flag indicates if method 'create()' is disabled or
	 * not.
	 * It can also have a string as value and it will be used as method name
	 * when its related exception is raised.
	 */
	protected $_DisableCreate = false;
	/**
	 * @var mixed[string] Sub-representation associated columns
	 * specifications.
	 */
	protected $_ExtendedColumns = [];
	/**
	 * @var string Name of a field containing IDs (without prefix).
	 */
	protected $_IDColumn = '';
	/**
	 * @var string Name of a field containing names (without prefix).
	 */
	protected $_NameColumn = 'name';
	/**
	 * @var string[string] List of fields (without prefix) associated to a
	 * sorting direction.
	 */
	protected $_OrderBy = false;
	/**
	 * @var string[] List of fields that can't be alter by generic accessors.
	 */
	protected $_ReadOnlyColumns = [];
	/**
	 * @var string Name of a \TooBasic\Representations\ItemRepresentation
	 * class.
	 */
	protected $_RepresentationClass = '';
	/**
	 * @var mixed[string] List of other representations that use current one
	 * as grouping item.
	 */
	protected $_SubLists = [];
	/**
	 * @var string Represented table's name (without prefix).
	 */
	protected $_Table = '';
	//
	// Magic methods.
	/**
	 * This method provides easy access to core property values.
	 *
	 * @param string $name Property name to look for.
	 * @return mixed Returns the value of the request property or FALSE if not
	 * found.
	 */
	public function __get($name) {
		$propName = "_{$name}";
		return isset($this->{$propName}) ? $this->{$propName} : false;
	}
	public function __set($name, $value) {
		$propName = "_{$name}";
		if(isset($this->{$propName})) {
			$this->{$propName} = $value;
		}
		return $this->{$propName};
	}
	//
	// Public class methods.
	/**
	 * This method is the one in charge of searching, loading and keeping
	 * track of all requested representation classes.
	 *
	 * @param string $name Name to use as search pattern for a representaion.
	 * It may be prefixed with a namespace.
	 * @return string Returns the requested representation's class.
	 * @throws \TooBasic\Exception
	 */
	public static function GetCoreProps($name) {
		//
		// If it was never requeste, it should be loaded, otherwise, an
		// internal list is used.
		if(!isset(self::$_LoadedInstances[$name])) {
			//
			// Guessing class name.
			$className = Names::CorePropsClass($name);
			//
			// Checking if the class was loaded by an external
			// mechanism.
			if(!class_exists($className)) {
				//
				// Guessing class definition file.
				$filename = Paths::Instance()->representationPath(Names::CorePropsFilename($name));
				//
				// Checking the file path existence.
				if($filename) {
					//
					// Loading the file
					require_once $filename;
					//
					// Checking if the class was successfully
					// loaded.
					if(!class_exists($className)) {
						throw new Exception(Translate::Instance()->EX_undefined_class(['name' => $className]));
					}
					//
					// Loading object.
					self::$_LoadedInstances[$name] = new $className();
				} else {
					//
					// Guessing JSON definition file.
					$filename = Paths::Instance()->representationPath($name, false, Paths::ExtensionJSON);
					if($filename) {
						//
						// Loading generic object.
						self::$_LoadedInstances[$name] = new CorePropsJSON();
						self::$_LoadedInstances[$name]->load($filename);
					} else {
						throw new Exception(Translate::Instance()->EX_cannot_load_core_props_class(['name' => $className]));
					}
				}
			}
		}
		//
		// Returning the found instance.
		return self::$_LoadedInstances[$name];
	}
}
