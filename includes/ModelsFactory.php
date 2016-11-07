<?php

/**
 * @file ModelsFactory.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\Translate;

/**
 * @class ModelsFactory
 * This class is the one int charge of constructing all model classes and
 * returning as simple instances or managing them as simgletons.
 */
class ModelsFactory extends Singleton {
	//
	// Protected properties.
	protected $_loadedClasses = [];
	protected $_singletons = [];
	//
	// Magic methods.
	/**
	 * This magic method provides an easy way to get models.
	 *
	 * @param string $className Base class name. If your model is called
	 * 'PagesModel', just say 'pages'.
	 * @return \TooBasic\Model Returns the requested model or NULL when it
	 * doesn't exist.
	 */
	public function __get($className) {
		//
		// Forwarding call and assuming no namespace.
		return $this->get($className, false);
	}
	public function __call($className, $params) {
		$namespace = isset($params[0]) ? $params[0] : false;
		//
		// Forwarding call.
		return $this->get($className, $namespace);
	}
	//
	// Public methods.
	/**
	 * This method looks for and returns a model object on success.
	 *
	 * @param string $basicClassName Base class name. If your model is called
	 * 'PagesModel', just say 'pages'.
	 * @param string $namespace If the model is defined inside a namespace, it
	 * can be specified with this parameter.
	 * @return \TooBasic\Model Returns the requested model or NULL when it
	 * doesn't exist.
	 */
	public function get($basicClassName, $namespace = false) {
		//
		// Default values.
		$out = null;
		//
		// Cleaning name space.
		$namespace = $namespace ? "\\{$namespace}\\" : '\\';
		while(strpos($namespace, '\\\\') !== false) {
			$namespace = str_replace('\\\\', '\\', $namespace);
		}
		//
		// Cleaning and building the real class name for the model.
		$className = Names::ModelClass($namespace.$basicClassName);
		$classFileName = Names::ModelFilename($basicClassName);
		//
		// If it's already stored as a singleton, that instance is
		// returned.
		// If it's not, but it's file is already loaded, it probably is
		// not a singleton, so it is created as a new object and then
		// returned.
		// Otherwise, the real deal is executed.
		if(isset($this->_singletons[$className])) {
			$out = $this->_singletons[$className];
		} elseif(in_array($className, $this->_loadedClasses)) {
			$out = new $className();
		} else {
			$out = $this->loadAndGet($classFileName, $className);
		}
		//
		// Returning what was found.
		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method holds the logic to find a class definition file for a
	 * model, load it and create the requested model as a simple instances or
	 * a singleton.
	 *
	 * @param string $classFileName Base name for the file to look for.
	 * @param string $className Class name of the object to build including
	 * its namespace.
	 * @return \TooBasic\Model Return the requested model or NULL if it wasn't
	 * found.
	 */
	protected function loadAndGet($classFileName, $className) {
		//
		// Default values.
		$out = null;
		//
		// Loading the full path.
		$filename = Paths::Instance()->modelPath($classFileName);
		//
		// Checking if it has its file.
		if($filename) {
			//
			// Loading the class definition file.
			require_once $filename;
			//
			// Checking if the class was actually defined.
			if(class_exists($className)) {
				//
				// Setting class definition file as already
				// loaded.
				$this->_loadedClasses[] = $className;
				//
				// Creating an instance of the class.
				$out = new $className();
				//
				// If the instance must act as a singleton, it's
				// stored in the known singletons list.
				if($out->isSingleton()) {
					$this->_singletons[$className] = $out;
				}
			} else {
				//
				// If the requested class was not defined, it is
				// considered a fatal exception.
				throw new Exception(Translate::Instance()->EX_undefined_class(['name' => $className]));
			}
		} else {
			//
			// If the requested class has no file, it is considered a
			// fatal exception.
			throw new Exception(Translate::Instance()->EX_cannot_open_model_file(['filename' => $classFileName]));
		}
		//
		// Retruning what was found.
		return $out;
	}
}
