<?php

class TooBasic_AssetsManager {
	//
	// Constants.
	const BACKUP_SUFFIX = '_AMANAGER_BACKUP';
	//
	// Protected properties.
	protected $_assetDirectories = array();
	protected $_assetFiles = array();
	protected $_isLoaded = false;
	//
	// Public methods.
	public function loadAssetsOf($path) {
		if(!$this->_isLoaded) {
			$this->_isLoaded = true;

			$ok = true;
			$manifest = false;
			//
			// Guessing names.
			$caseName = preg_replace('/\.([a-z]*)$/', '', preg_replace('/_([_]+)/', '_', 'case_'.str_replace('/', '_', substr($path, strlen(TOOBASIC_TESTS_DIR)))));
			$caseFolder = TOOBASIC_TESTS_ACASES_DIR."/{$caseName}";
			$manifestPath = "{$caseFolder}/manifest.json";

			echo "\nSetting up for '{$caseName}'... ";
			//
			// Loading manifest.
			if($ok && is_readable($manifestPath)) {
				$manifest = json_decode(file_get_contents($manifestPath));
				if(!$manifest) {
					$ok = false;
				}
			} else {
				echo "Done (No settings for it)\n";
				$ok = false;
			}
			//
			// Creating needed asset directories.
			if($ok && isset($manifest->assetDirectories)) {
				foreach($manifest->assetDirectories as $path) {
					$fullPath = ROOTDIR.$path;

					if(!is_dir($fullPath)) {
						mkdir($fullPath, 0777, true);
						$this->_assetDirectories[] = $fullPath;
					}
				}
			}
			//
			// Copying assets.
			if($ok && isset($manifest->assets)) {
				foreach($manifest->assets as $asset) {
					$fromPath = $caseFolder.$asset;
					$toPath = ROOTDIR.$asset;

					if(is_file($toPath)) {
						rename($toPath, $toPath.self::BACKUP_SUFFIX);
					}

					copy($fromPath, $toPath);

					$this->_assetFiles[] = $toPath;
				}
			}
			//
			// Considering generated assets.
			if($ok && isset($manifest->generatedAssets)) {
				foreach($manifest->generatedAssets as $asset) {
					$this->_assetFiles[] = ROOTDIR.$asset;
				}
			}

			if($ok) {
				echo "Done\n";
			}
		}
	}
	public function tearDown() {
		foreach($this->_assetFiles as $path) {
			if(is_file($path)) {
				unlink($path);
				if(is_file($path.self::BACKUP_SUFFIX)) {
					rename($path.self::BACKUP_SUFFIX, $path);
				}
			}
		}
		foreach(array_reverse($this->_assetDirectories) as $path) {
			rmdir($path);
		}
	}
}
