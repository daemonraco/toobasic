<?php

/**
 * @file SApiManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Paths;
use TooBasic\SApiReaderException;
use TooBasic\Translate;

/**
 * @class SApiManager
 * This class is the access point for all API readers.
 */
class SApiManager extends \TooBasic\Managers\Manager {
	//
	// Protected properties.
	protected $_knownReaders = array();
	//
	// Magic methods.
	/**
	 * This method provides a simple way to access a API reader by using the
	 * configuration file name as a property of this manager.
	 * Nontheless, if the name matches a magic property it will return such
	 * property instead.
	 *
	 * @param string $name Reader name to retrieve.
	 * @return \TooBasic\SApiReader Returns a simple API reader.
	 */
	public function __get($name) {
		$out = parent::__get($name);
		return $out ? $out : $this->get($name);
	}
	//
	// Public methods.
	/**
	 * This method provides access to a Simple API Reader. It loads a Simple
	 * API Reader configuration file into the proper reader and returns it.
	 *
	 * @param string $name Reader name to retrieve.
	 * @return \TooBasic\SApiReader Returns a simple API reader.
	 * @throws SApiReaderException
	 */
	public function get($name) {
		//
		// Default values.
		$reader = false;
		//
		// Checking if it's a reader already loaded to avoid multiple
		// loads.
		if(!isset($this->_knownReaders[$name])) {
			//
			// Global dependencies.
			global $SApiReader;
			//
			// Loading full configuration.
			$json = self::LoadConfiuration($name);
			//
			// Creating the proper reader.
			$class = $SApiReader[GC_SAPIREADER_TYPES][$json->type];
			$reader = new $class($json);
			$this->_knownReaders[$name] = $reader;
		} else {
			$reader = $this->_knownReaders[$name];
		}

		return $reader;
	}
	//
	// Protected class methods.
	/**
	 * This class method holds the logic to load a JSON configuration from
	 * files and also extend it with some subfiles.
	 *
	 * @param string $name Name of the configuration file to load.
	 * @return \stdClass Returns a configuration structure or FALSE when it's
	 * not found.
	 * @throws SApiReaderException
	 */
	protected static function LoadConfiuration($name) {
		//
		// Default values.
		$json = false;
		//
		// Global dependencies.
		global $Paths;
		//
		// Shortcuts.
		$pathsMgr = Paths::Instance();
		//
		// Searching main files.
		$path = $pathsMgr->customPaths($Paths[GC_PATHS_SAPIREADER], $name, Paths::ExtensionJSON);
		if($path) {
			//
			// Global dependencies.
			global $SApiReader;
			//
			// Loading main configuration.
			$json = self::LoadJSON($path);
			//
			// Keeping track of the file.
			$json->path = $path;
			//
			// Extensions track.
			$json->extended = array();
			//
			// Setting default abstract state.
			if(!isset($json->abstract)) {
				$json->abstract = false;
			}
			//
			// Loading extensions.
			while(isset($json->extends)) {
				//
				// Checking what is to be extendend.
				$extends = $json->extends;
				//
				// Extension configuration must be an array.
				if(!is_array($extends)) {
					$extends = array($extends);
				}
				//
				// Removing extensions configuration from the
				// final result.
				unset($json->extends);
				//
				// Loading extensions.
				foreach($extends as $subName) {
					//
					// Searching extension file.
					$exPath = $pathsMgr->customPaths($Paths[GC_PATHS_SAPIREADER], $subName, Paths::ExtensionJSON);
					//
					// Saving track.
					$json->extended[$subName] = $exPath;
					//
					// Checking path.
					if($exPath) {
						//
						// Loading extension configuration.
						$exJson = self::LoadJSON($exPath);
						//
						// Extending.
						foreach($exJson as $prop => $value) {
							if(!isset($json->{$prop})) {
								$json->{$prop} = $value;
							}
						}
						//
						// Extending one more level.
						foreach(array('services', 'headers') as $lProp) {
							//
							// Enforcing properties presence.
							if(!isset($exJson->{$lProp})) {
								$exJson->{$lProp} = new \stdClass();
							}
							//
							// Copying unknown properties.
							foreach($exJson->{$lProp} as $prop => $value) {
								if(!isset($json->{$lProp}->{$prop})) {
									$json->{$lProp}->{$prop} = $value;
								}
							}
						}
					} else {
						throw new SApiReaderException("Path '{$path}' extends '{$subName}' but it doesn't exist.");
					}
				}
			}
			//
			// Setting default type.
			if(!isset($json->type)) {
				$json->type = $SApiReader[GC_SAPIREADER_DEFAULT_TYPE];
			}
			//
			// Checking type.
			if(!isset($SApiReader[GC_SAPIREADER_TYPES][$json->type])) {
				throw new SApiReaderException($this->tr->EX_unhandled_api_type(['type' => $json->type]));
			}
		} else {
			throw new SApiReaderException("Unable to find configuration '{$name}'.");
		}
		return $json;
	}
	/**
	 * This class methods loads and attempts to decode a JSON configuarion
	 * file.
	 *
	 * @param string $path Path where the configuration is stored.
	 * @return \stdClass Returns a configuration structure.
	 * @throws SApiReaderException
	 */
	protected static function LoadJSON($path) {
		//
		// Loading and decoding.
		$json = json_decode(file_get_contents($path));
		//
		// Checking for errors.
		if(!$json) {
			throw new SApiReaderException(Translate::Instance()->EX_JSON_invalid_file([
				'path' => $path,
				'errorcode' => json_last_error(),
				'error' => json_last_error_msg()
			]));
		}

		return $json;
	}
}
