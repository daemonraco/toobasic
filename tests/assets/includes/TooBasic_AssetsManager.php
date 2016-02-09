<?php

class TooBasic_AssetsManager {
	//
	// Constants.
	const BACKUP_SUFFIX = '_AMANAGER_BACKUP';
	//
	// Public class properties.
	public static $Verbose = false;
	//
	// Protected properties.
	protected $_assetDirectories = array();
	protected $_assetFiles = array();
	protected $_generatedAssetFiles = array();
	protected $_isLoaded = false;
	protected $_tearDownScripts = array();
	//
	// Public methods.
	public function assetDirectories() {
		return $this->_assetDirectories;
	}
	public function assetFiles() {
		return $this->_assetFiles;
	}
	public function generatedAssetFiles() {
		return $this->_generatedAssetFiles;
	}
	public function loadAssetsOf($path) {
		if(!$this->_isLoaded) {
			$this->_isLoaded = true;

			$ok = true;
			$manifest = false;
			//
			// Guessing names.
			$nameReplacements = array(
				'%-%' => '',
				'%/%' => '_',
				'/\.([a-z]*)$/' => '',
				'/_([_]+)/' => '_'
			);
			$caseName = 'case_'.substr($path, strlen(TOOBASIC_TESTS_DIR));
			foreach($nameReplacements as $k => $v) {
				$caseName = preg_replace($k, $v, $caseName);
			}
			$caseFolder = TOOBASIC_TESTS_ACASES_DIR."/{$caseName}";
			$manifestPath = "{$caseFolder}/manifest.json";
			if(self::$Verbose) {
				echo "\nSetting up for '{$caseName}'... ";
			}
			//
			// Loading manifest.
			if($ok && is_readable($manifestPath)) {
				$manifest = json_decode(file_get_contents($manifestPath));
				if(!$manifest) {
					$ok = false;
				}
			} else {
				if(self::$Verbose) {
					echo "Done (No settings for it)\n";
				}
				$ok = false;
			}
			//
			// Creating needed asset directories.
			if($ok && isset($manifest->assetDirectories)) {
				foreach($manifest->assetDirectories as $path) {
					$fullPath = TESTS_ROOTDIR.$path;

					if(!is_dir($fullPath)) {
						mkdir($fullPath, 0777, true);
						$this->_assetDirectories[] = $fullPath;
					}
				}
			}
			$travisReplacements = array();
			if($ok) {
				foreach(get_defined_constants() as $constant => $value) {
					if(preg_match('/^TRAVISCI_/', $constant)) {
						$travisReplacements["%{$constant}%"] = $value;
					}
				}
			}
			//
			// Copying assets.
			if($ok && isset($manifest->assets)) {
				foreach($manifest->assets as $asset) {
					$fromPath = $caseFolder.$asset;
					$toPath = TESTS_ROOTDIR.$asset;

					if(is_file($toPath)) {
						rename($toPath, $toPath.self::BACKUP_SUFFIX);
					}

					file_put_contents($toPath, str_replace(array_keys($travisReplacements), array_values($travisReplacements), file_get_contents($fromPath)));

					$this->_assetFiles[] = $toPath;
				}
			}
			//
			// Considering generated assets.
			if($ok && isset($manifest->generatedAssets)) {
				foreach($manifest->generatedAssets as $asset) {
					$this->_assetFiles[] = TESTS_ROOTDIR.$asset;
					$this->_generatedAssetFiles[] = TESTS_ROOTDIR.$asset;
				}
			}

			if($ok && isset($manifest->setUpScripts)) {
				if(!is_array($manifest->setUpScripts)) {
					$manifest->setUpScripts = array($manifest->setUpScripts);
				}
				foreach($manifest->setUpScripts as $script) {
					$scriptParts = explode(':', $script);
					$scriptPath = false;
					if($scriptParts[0] == 'G') {
						$scriptPath = TOOBASIC_TESTS_ACASES_DIR."/scripts/{$scriptParts[1]}";
					} else {
						$scriptPath = "{$caseFolder}/{$scriptParts[0]}";
					}

					chmod($scriptPath, 0755);
					passthru($scriptPath);
				}
			}
			if($ok && isset($manifest->tearDownScripts)) {
				if(!is_array($manifest->tearDownScripts)) {
					$manifest->tearDownScripts = array($manifest->tearDownScripts);
				}
				foreach($manifest->tearDownScripts as $script) {
					$scriptParts = explode(':', $script);
					$scriptPath = false;
					if($scriptParts[0] == 'G') {
						$scriptPath = TOOBASIC_TESTS_ACASES_DIR."/scripts/{$scriptParts[1]}";
					} else {
						$scriptPath = "{$caseFolder}/{$scriptParts[0]}";
					}

					chmod($scriptPath, 0755);
					$this->_tearDownScripts[] = $scriptPath;
				}
			}

			if($ok && self::$Verbose) {
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
		foreach($this->_tearDownScripts as $script) {
			passthru($script);
		}
	}
}
