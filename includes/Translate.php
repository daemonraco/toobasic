<?php

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
	public function get($key, $params = array()) {
		$out = "@{$key}";

		$this->load();
		if(!isset(Params::Instance()->debugnolang)) {
			if(isset($this->_tr[$key])) {
				$out = $this->_tr[$key];

				foreach($params as $name => $value) {
					$out = str_replace("%{$name}%", (string)$value);
				}
			}
		}

		return $out;
	}
	//
	// Protected methods.
	protected function init() {
		global $Defaults;

		$this->_currentLang = $Defaults["langs-defaultlang"];
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
