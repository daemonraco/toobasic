<?php

/**
 * @file Manifest.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases.
use TooBasic\Exception;
use TooBasic\Managers\ManifestsManager;
use TooBasic\Sanitizer;

/**
 * @class Manifest
 */
class Manifest {
	//
	// Constants.
	const ErrorOK = 0;
	const ErrorPHPVersion = 1;
	const ErrorTooBasciVersion = 2;
	//
	// Protected properties.
	/**
	 * @var mixed[] List of found errors while checking.
	 */
	protected $_errors = array();
	/**
	 * @var \stdClass Information held by this manifest.
	 */
	protected $_information = false;
	/**
	 * @var string Name of the module represented.
	 */
	protected $_moduleName = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $moduleName Name of the module from which to load a
	 * manifest information.
	 */
	public function __construct($moduleName) {
		$this->_moduleName = $moduleName;

		$this->load();
	}
	//
	// Public methods.
	/**
	 * This method checks for versions requirements.
	 *
	 * @return boolean Returns TRUE if it detected no errors.
	 */
	public function check() {
		//
		// Checking required PHP version.
		if(version_compare(self::CleanVersion(PHP_VERSION), self::CleanVersion($this->_information->required_versions->php)) < 0) {
			$this->setError(self::ErrorPHPVersion, "This module requires at least version {$this->_information->required_versions->php} of PHP");
		}
		//
		// Checking required TooBasic version.
		if(version_compare(self::CleanVersion(TOOBASIC_VERSION), self::CleanVersion($this->_information->required_versions->toobasic)) < 0) {
			$this->setError(self::ErrorPHPVersion, "This module requires at least version {$this->_information->required_versions->toobasic} of TooBasic");
		}
		//
		// Checking cross-module dependencies.
		foreach($this->_information->required_versions as $field => $reqVersion) {
			$matches = false;
			//
			// Detecting module dependency and name.
			if(preg_match('/^mod:(?P<ucode>.+)$/', $field, $matches)) {
				//
				// Looking for the requested module.
				$manifest = ManifestsManager::Instance()->manifestByUCode($matches['ucode']);
				//
				// Checking installation.
				if($manifest) {
					//
					// Comparing version.
					$modVersion = $manifest->information()->version;
					if(version_compare(self::CleanVersion($modVersion), self::CleanVersion($reqVersion)) < 0) {
						$this->setError(self::ErrorPHPVersion, "Required version '{$reqVersion}' for module '{$manifest->information()->name}' (found version '{$modVersion}')");
					}
				} else {
					$this->setError(self::ErrorPHPVersion, "Unable to obtain version number for module 'UCODE:{$matches['ucode']}'. Please make sure it is installed?");
				}
			}
		}

		return !$this->hasErrors();
	}
	/**
	 * This method provides access a list of errors found while loading or
	 * checking.
	 *
	 * @return mixed[] Returns a list of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method allows to know if there was an error while loading or
	 * checking.
	 *
	 * @return boolean Returns TRUE if at least one error was found.
	 */
	public function hasErrors() {
		return \boolval($this->_errors);
	}
	/**
	 * This method provides access to the loaded manifest information.
	 *
	 * @return \stdClass Returns a manifest information.
	 */
	public function information() {
		return $this->_information;
	}
	//
	// Protected methods.
	/**
	 * This method loads all the information in a manifest file associated
	 * with a module.
	 * In the case that some or all the information is not specified, it will
	 * build the required information using defaults.
	 */
	protected function load() {
		//
		// Avoiding multiple loads.
		if($this->_information === false) {
			//
			// Default values.
			$this->_information = new \stdClass();
			$json = false;
			//
			// Global dependencies.
			global $Directories;
			//
			// Loading file.
			$path = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$this->_moduleName}/manifest.json");
			if(is_readable($path)) {
				$json = json_decode(file_get_contents($path));
				if(!$json) {
					throw new Exception("Unable to parse manifest of module '{$this->_moduleName}'");
				}
			} else {
				$json = new \stdClass();
			}
			//
			// Enforcing main object.
			\TooBasic\objectCopyAndEnforce(array('name', 'ucode', 'version', 'description', 'author', 'copyright', 'license', 'url', 'url_doc', 'required_versions'), $json, $this->_information, array(
				'author' => new \stdClass(),
				'required_versions' => new \stdClass()
			));
			//
			// Enforcing author's information.
			\TooBasic\objectCopyAndEnforce(array('name', 'page'), $this->_information->author, $this->_information->author);
			//
			// Enforcing required versions information.
			\TooBasic\objectCopyAndEnforce(array('php', 'toobasic'), $this->_information->required_versions, $this->_information->required_versions, array(
				'php' => phpversion(),
				'toobasic' => TOOBASIC_VERSION
			));
			//
			// Enforcing name, universal code and version number.
			if(!$this->_information->name) {
				$this->_information->name = ucwords($this->_moduleName);
			}
			if(!$this->_information->ucode) {
				$this->_information->ucode = $this->_information->name;
			}
			$this->_information->ucode = strtolower($this->_information->ucode);
			if(!$this->_information->version) {
				$this->_information->version = '0.0.1';
			}
			//
			// Enforcing documentation url.
			if(!$this->_information->url_doc) {
				$this->_information->url_doc = $this->_information->url;
			}
		}
	}
	/**
	 * This method adds an error to the internal list.
	 *
	 * @param int $code Error code.
	 * @param string $message Message explaining the error.
	 */
	protected function setError($code, $message) {
		$this->_errors[] = array(
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message
		);
	}
	//
	// Protected class methods.
	protected static function CleanVersion($versionNumber) {
		$expanded = explode('-', $versionNumber);
		return array_shift($expanded);
	}
}
