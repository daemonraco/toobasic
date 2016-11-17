<?php

/**
 * @file Translate.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\Exception;

/**
 * @class Translate
 * This singleton class holds the logic to manage and interact with language
 * translations.
 */
class Translate extends Singleton {
	//
	// Protected properties.
	/**
	 * @var string[string] This is the list of configured translation keys and
	 * their values.
	 */
	protected $_tr = [];
	/**
	 * @var string Acronym of the current language.
	 */
	protected $_currentLang = false;
	/**
	 * @var boolean This flag gets true when the list of translations keys
	 * where loaded for the current language.
	 */
	protected $_loaded = false;
	//
	// Magic methods.
	/**
	 * This method allows to access a translation key value using it as a
	 * property of this singleton.
	 *
	 * @param string $key Key to access.
	 * @return string Return a translated result for the key.
	 */
	public function __get($key) {
		return $this->get($key);
	}
	/**
	 * This method allows to use each key as a function.
	 * 
	 * @param string $key Key to translate
	 * @param mixed[] $arguments List of parameters to use with a key.
	 * @return string Returns the translated key.
	 */
	public function __call($key, $arguments) {
		//
		// Default values.
		$params = [];
		//
		// If there's at least one argument they're analysed.
		if(isset($arguments[0])) {
			//
			// If the first argument is an array, it is the list of
			// params.
			// Othere, all arguments are taken as params.
			if(is_array($arguments[0])) {
				$params = $arguments[0];
			} else {
				if(count($arguments) % 2 != 0) {
					throw new Exception("The amount of parameters given for key '{$key}' is not pair");
				}
				while($arguments) {
					$pKey = array_shift($arguments);
					$pValue = array_shift($arguments);
					$params[$pKey] = $pValue;
				}
			}
		}
		//
		// Attempting to translate the key.
		return $this->get($key, $params);
	}
	//
	// Public methods.
	/**
	 * This method is capable of loading all language configurations and merge
	 * them into one for each language storing it inside 'ROOTDIR/cache'.
	 * This mechanism provides a way to have a single file with translation
	 * improving the loading time.
	 *
	 * @global type $Directories
	 * @global type $Paths
	 * @return type
	 */
	public function compileLangs() {
		//
		// Default values.
		$results = [
			GC_AFIELD_COUNTS => [
				GC_AFIELD_KEYS => 0,
				GC_AFIELD_KEYS_BY_LANG => []
			],
			GC_AFIELD_LANGS => [],
			GC_AFIELD_FILES => [],
			GC_AFIELD_COMPILATIONS => []
		];
		$filePaths = [];
		$files = [];
		//
		// Global dependencies.
		global $Directories;
		global $Paths;
		//
		// Generating the destination directory name.
		$compDir = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$Paths[GC_PATHS_LANGS]}");
		//
		// Loading all known language configuration files.
		foreach(array_reverse(Paths::Instance()->langNonBuiltPaths()) as $dirpath) {
			$filePaths += glob(Sanitizer::DirPath("{$dirpath}/*.json"));
		}
		//
		// Cleaning possible duplicates.
		$filePaths = array_unique($filePaths);
		//
		// Scaning each file name and grouping them by languague.
		foreach($filePaths as $path) {
			//
			// Checking configuration file permissons.
			if(!is_readable($path)) {
				/** @todo this should raise a TooBasicException instead of breaking the loop. */
				break;
			}
			//
			// Loading path information.
			$info = pathinfo($path);
			//
			// Checking language group existence.
			if(!isset($files[$info['filename']])) {
				$files[$info['filename']] = [];
			}
			//
			// Adding it to the list.
			$files[$info['filename']][] = $path;
		}
		//
		// Taking the list of languages for the results report.
		$results[GC_AFIELD_LANGS] = array_keys($files);
		//
		// Also the grouped list of files.
		$results[GC_AFIELD_FILES] = $files;
		//
		// Compiling each language.
		foreach($files as $lang => $paths) {
			//
			// Compiled file path.
			$compFile = Sanitizer::DirPath("{$compDir}/{$lang}.json");
			//
			// Creating a structure to hold translations.
			$compStructure = new \stdClass();
			//
			// Timestamp mark.
			$compStructure->compiled = time();
			//
			// Loading each translation file.
			$keys = [];
			foreach($paths as $path) {
				//
				// Loading and decoding each file.
				$json = json_decode(file_get_contents($path));
				//
				// Checking loaded file structure.
				if(isset($json->keys)) {
					//
					// Adding each key.
					foreach($json->keys as $tr) {
						$keys[$tr->key] = $tr->value;
					}
				}
			}
			//
			// Sorting loaded keys.
			ksort($keys);
			//
			// Storing the count of keys on the report (by language
			// and overall).
			$results[GC_AFIELD_COUNTS][GC_AFIELD_KEYS_BY_LANG][$lang] = count($keys);
			$results[GC_AFIELD_COUNTS][GC_AFIELD_KEYS] += $results[GC_AFIELD_COUNTS][GC_AFIELD_KEYS_BY_LANG][$lang];
			//
			// Converting keys into objects (compilation).
			$keysObj = [];
			foreach($keys as $key => $value) {
				$aux = new \stdClass();
				$aux->key = $key;
				$aux->value = $value;

				$keysObj[] = $aux;
			}
			//
			// Storing keys in the compiled object.
			$compStructure->keys = $keysObj;
			//
			// Saving generated file.
			file_put_contents($compFile, json_encode($compStructure));
			//
			// Adding compiled file path to the report.
			$results[GC_AFIELD_COMPILATIONS][] = $compFile;
		}
		//
		// Returning a results report.
		return $results;
	}
	/**
	 * This method gets the translation on the current language for a certain
	 * key.
	 *
	 * @param string $key Key to translate.
	 * @param string[string] $params List of replacements to use on
	 * translation.
	 * @return string Returns a translation result.
	 */
	public function get($key, $params = []) {
		//
		// Default values.
		$out = "@{$key}";
		//
		// Checking if translations are disabled by debugs.
		if(!isset(Params::Instance()->debugnolang)) {
			//
			// Enforcing translations loading.
			$this->load(true);
			//
			// Checking if it's a known key.
			if(isset($this->_tr[$key])) {
				//
				// Obtaning basic translation
				$out = $this->_tr[$key];
				//
				// '$params' must be an array.
				if(!is_array($params)) {
					$params = [];
				}
				//
				// Replacing each parameter.
				foreach($params as $name => $value) {
					$out = str_replace("%{$name}%", (string)$value, $out);
				}
			}
		}
		//
		// Retruning translation.
		return $out;
	}
	/**
	 * This method checks if a translation key is present on the current
	 * language.
	 *
	 * @param string $key Key to check.
	 * @return bool Returns true when it's present.
	 */
	public function has($key) {
		//
		// Enforcing translations loading.
		$this->load(true);
		//
		// Checking if it's a known key.
		return isset($this->_tr[$key]);
	}
	//
	// Protected methods.
	/**
	 * Singleton's initializer.
	 */
	protected function init() {
		//
		// Global dependencies.
		global $LanguageName;
		//
		// Catching current language.
		$this->_currentLang = $LanguageName ? $LanguageName : \TooBasic\guessLanguage();
	}
	/**
	 * This method loads configurated translation for the current language.
	 *
	 * @param boolean $required When true it abort's if there's no translation
	 * files for the current language.
	 */
	protected function load($required = false) {
		//
		// Avoiding multiple loads.
		if(!$this->_loaded) {
			//
			// Obtaining which language files have to be loaded.
			$langPaths = Paths::Instance()->langPaths($this->_currentLang);
			//
			// If there are no files and their are required, it
			// aborts and displayes a error message.
			if(!$langPaths && $required) {
				throw new Exception("Unable to find translation files for language '{$this->_currentLang}'");
			}
			//
			// Loading each file.
			foreach($langPaths as $path) {
				$this->loadFile($path);
			}
			//
			// Language debug.
			if(isset(Params::Instance()->debuglang)) {
				\TooBasic\debugThing(function() use ($langPaths) {
					echo "Current language: '{$this->_currentLang}'\n\n";

					echo "Translation files:\n";
					foreach($langPaths as $path) {
						echo "\t- '{$path}'\n";
					}

					echo "\nTranslation keys:\n";
					ksort($this->_tr);
					foreach($this->_tr as $key => $value) {
						echo "\t- '{$key}': '{$value}'\n";
					}
				});
				die;
			}
			//
			// Setting loading flag as loaded.
			$this->_loaded = true;
		}
	}
	/**
	 * This method loads translations from a single file.
	 *
	 * @param string $path Path to be loaded.
	 */
	protected function loadFile($path) {
		//
		// Default values.
		$error = false;
		//
		// Physically loading translation file.
		$json = json_decode(file_get_contents($path));
		//
		// Checking JSON errors.
		if(!$json || get_class($json) != 'stdClass') {
			throw new Exception("Path '{$path}' is not a valid JSON ([".json_last_error()."] ".json_last_error_msg().")");
		}
		//
		// If there were no errors, it loads each translation key.
		if(!$error) {
			foreach($json->keys as $pair) {
				$this->_tr[$pair->key] = $pair->value;
			}
		}
	}
}
