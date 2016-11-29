<?php

/**
 * @file ItemsFactoryProvider.php
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
 * @class ItemsFactoryProvider
 * This singleton class provides a method to fetch a representation factory using
 * it's name regardless of its physical location.
 */
class ItemsFactoryProvider extends \TooBasic\Singleton {
	//
	// Protected properties.
	/**
	 * @var string[string] List of already loaded factory's class names.
	 */
	protected $_loadedClases = [];
	//
	// Magic methods.
	/**
	 * This method is an alias for 'get()'.
	 *
	 * @param string $name Name to use as search pattern for a factory.
	 * @return \TooBasic\Representations\ItemsFactory Returns the requested
	 * factory or NULL when it's not found.
	 */
	public function __get($name) {
		//
		// Forwarding the request.
		return $this->get($name);
	}
	/**
	 * This method provides a way to use fatory's names as methods of this
	 * singleton. The database connection name to use may be passed as a
	 * parameter and also a namespace as a second parameter.
	 *
	 * @param string $name Name to use as search pattern for a factory.
	 * @param string[] $args List of parameters to use when forwarding the
	 * call.
	 * @return \TooBasic\Representations\ItemsFactory Returns the requested
	 * factory or NULL when it's not found.
	 */
	public function __call($name, $args) {
		//
		// Is there a namespace specified, a proper name should be built.
		if(isset($args[1])) {
			$namespace = "{$args[1]}\\";
			while(strpos($namespace, '\\\\') !== false) {
				$namespace = str_replace('\\\\', '\\', $namespace);
			}
			$name = $namespace.$name;
		}
		//
		// Forwarding the request.
		return $this->get($name, isset($args[0]) ? $args[0] : false);
	}
	/**
	 * This method provides access to a certain items representation factory.
	 *
	 * @param string $name Name to use as search pattern for a factory. It may
	 * be prefixed with a namespace.
	 * @param string $dbname If the factory must work on a database that is
	 * not the default one, this parameter provides a way to specify the right
	 * connection.
	 * @return \TooBasic\Representations\ItemsFactory Returns the requested
	 * factory or NULL when it's not found.
	 */
	public function get($name, $dbname = false) {
		//
		// Obtaining the right class name and forcing its loading when
		// necessary.
		$fullName = $this->loadClass($name);
		//
		// Returning the instance of the requested factory.
		return $fullName ? $fullName::Instance($dbname) : null;
	}
	//
	// Protected methods.
	/**
	 * This method is the one in charge of searching, loading and keeping
	 * track of all requested factories.
	 *
	 * @param string $name Name to use as search pattern for a factory. It may
	 * be prefixed with a namespace.
	 * @return string Returns the requested factory's class name or FALSE when
	 * it's not found.
	 * @throws \TooBasic\Exception
	 */
	protected function loadClass($name) {
		//
		// Default values.
		$out = false;
		//
		// Guessing the class name.
		$className = Names::ItemsFactoryClass($name);
		//
		// If it was never loaded, it should give it a try, otherwise, an
		// internal list is used.
		if(!isset($this->_loadedClases[$className])) {
			//
			// Checking if the class was loaded by an external
			// mechanism.
			if(class_exists($className)) {
				//
				// Setting class as loaded.
				$this->_loadedClases[$name] = $className;
				$out = $className;
			} else {
				//
				// Guessing the file path where the class might be
				// stored.
				$filename = Paths::Instance()->representationPath(Names::ItemsFactoryFilename($name));
				//
				// Checking the file path existence.
				if($filename) {
					//
					// Loading the file
					require_once $filename;
					//
					// Checking if the class was successfully
					// loaded.
					if(class_exists($className)) {
						//
						// Setting class as loaded.
						$this->_loadedClases[$name] = $className;
						$out = $className;
					} else {
						throw new Exception(Translate::Instance()->EX_undefined_class(['name' => $className]));
					}
				} else {
					throw new Exception(Translate::Instance()->EX_cannot_load_representation_factory_class(['name' => $className]));
				}
			}
		} else {
			$out = $this->_loadedClases[$className];
		}
		//
		// Returning the found class name.
		return $out;
	}
}
