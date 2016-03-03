<?php

/**
 * @file ConfigLoaderMulti.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Configs;

//
// Class aliases.
use TooBasic\Paths;

/**
 * @class ConfigLoaderMulti
 * This class holds the logic to load a single config files in a simple way.
 */
class ConfigLoaderMulti extends ConfigLoader {
	//
	// Protected methods.
	/**
	 * This method returns the list of config file paths to load.
	 *
	 * @return string[] Returns a list of absolute paths.
	 */
	protected function paths() {
		return Paths::Instance()->configPath($this->_config->name(), Paths::ExtensionJSON, true);
	}
}
