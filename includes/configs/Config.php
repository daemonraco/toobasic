<?php

/**
 * @file Config.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class Config
 * This class represent a JSON file and its properties providing a simpler access
 * and a controlled and easier loading mechanism.
 */
class Config extends \stdClass {
	//
	// Protected core properties.
	/**
	 * @var \TooBasic\Configs\ConfigLoader Config files loader shortcut.
	 */
	protected $_CP_loader = false;
	//
	// Protected properties.
	/**
	 * @var string This flag shows what mechanism was used to load a config
	 * file.
	 */
	protected $_mode = false;
	/**
	 * @var string This is the loaded configuration file name without
	 * extension.
	 */
	protected $_name = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $name Configuration file name (without extension).
	 * @param string $mode Mechanisme to use when loading.
	 * @throws \TooBasic\ConfigException
	 */
	public function __construct($name, $mode = GC_CONFIG_MODE_SIMPLE) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Storing basic values.
		$this->_name = $name;
		$this->_mode = $mode;
		//
		// Checking that the loading mechanism is a known one.
		if(!in_array($this->mode(), array_keys($Defaults[GC_DEFAULTS_CONFIG_LOADERS]))) {
			throw new ConfigException(Translate::Instance()->EX_unknown_mode(['mode' => $this->mode()]));
		}
		//
		// Loading physical files.
		$loaderClass = $Defaults[GC_DEFAULTS_CONFIG_LOADERS][$this->mode()];
		$this->_CP_loader = new $loaderClass($this);
		$this->_CP_loader->load();
	}
	//
	// Public methods.
	/**
	 * This method provides access to the mechanism name used to load.
	 *
	 * @return string Returns a mechanism name.
	 */
	public function mode() {
		return $this->_mode;
	}
	/**
	 * This method provides access to the loaded configuration file name.
	 *
	 * @return string Returns a file name without extension.
	 */
	public function name() {
		return $this->_name;
	}
	/**
	 * This methods converts current configuration object into a pure array
	 * structure and returns it.
	 *
	 * @return mixed[] Returns a multi-level array.
	 */
	public function toArray() {
		return json_decode(json_encode($this), true);
	}
}
