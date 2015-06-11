<?php

namespace TooBasic;

class Translate extends Singleton {
	//
	// Protected properties.
	protected $_tr = array();
	protected $_currentLang = false;
	protected $_loaded = false;
	//
	// Magic methods.
	public function __get($key) {
		return $this->get($key);
	}
	//
	// Public methods.
	public function compileLangs() {
		$results = array(
			"counts" => array()
		);

		global $Directories;
		global $Paths;

		$compDir = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_CACHE]}/{$Paths[GC_PATHS_LANGS]}");
		$filePaths = array();

		foreach(array_reverse(Paths::Instance()->langNonBuiltPaths()) as $dirpath) {
			$filePaths += glob(Sanitizer::DirPath("{$dirpath}/*.json"));
		}
		$filePaths = array_unique($filePaths);

		$files = array();
		foreach($filePaths as $path) {
			if(!is_readable($path)) {
				break;
			}

			$info = pathinfo($path);

			if(!isset($files[$info["filename"]])) {
				$files[$info["filename"]] = array();
			}
			$files[$info["filename"]][] = $path;
		}
		$results["langs"] = array_keys($files);
		$results["files"] = $files;

		$results["counts"]["keys"] = 0;
		$results["counts"]["keys-by-lang"] = array();
		$results["compilations"] = array();
		foreach($files as $lang => $paths) {
			$compFile = Sanitizer::DirPath("{$compDir}/{$lang}.json");
			$compStructure = new \stdClass();

			$compStructure->compiled = time();

			$keys = array();
			foreach($paths as $path) {
				$json = json_decode(file_get_contents($path));
				if(isset($json->keys)) {
					foreach($json->keys as $tr) {
						$keys[$tr->key] = $tr->value;
					}
				}
			}
			ksort($keys);

			$results["counts"]["keys-by-lang"][$lang] = count($keys);
			$results["counts"]["keys"] += $results["counts"]["keys-by-lang"][$lang];

			$keysObj = array();
			foreach($keys as $key => $value) {
				$aux = new \stdClass();
				$aux->key = $key;
				$aux->value = $value;

				$keysObj[] = $aux;
			}
			$compStructure->keys = $keysObj;

			file_put_contents($compFile, json_encode($compStructure));

			$results["compilations"][] = $compFile;
		}

		return $results;
	}
	public function get($key, $params = array()) {
		$out = "@{$key}";

		$this->load();
		if(!isset(Params::Instance()->debugnolang)) {
			if(isset($this->_tr[$key])) {
				$out = $this->_tr[$key];

				foreach($params as $name => $value) {
					$out = str_replace("%{$name}%", (string) $value);
				}
			}
		}

		return $out;
	}
	//
	// Protected methods.
	protected function init() {
		global $Defaults;

		$this->_currentLang = $Defaults[GC_DEFAULTS_LANGS_DEFAULTLANG];
	}
	protected function load() {
		if(!$this->_loaded) {
			foreach(Paths::Instance()->langPaths($this->_currentLang) as $path) {
				$this->loadFile($path);
			}

			$this->_loaded = true;
		}
	}
	protected function loadFile($path) {
		$error = false;

		$json = json_decode(file_get_contents($path));
		if(json_last_error() !== JSON_ERROR_NONE) {
			$error = true;
			debugit("[".json_last_error()."] ".json_last_error_msg(), true);
		}

		if(!$error) {
			foreach($json->keys as $pair) {
				$this->_tr[$pair->key] = $pair->value;
			}
		}
	}
}
