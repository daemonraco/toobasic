<?php

/**
 * @file ConfigLoaderSimple.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Configs;

//
// Class aliases.
use TooBasic\Paths;

/**
 * @class ConfigLoaderSimple
 * This class holds the logic to load multiple config files in a simple way.
 */
class ConfigLoaderSimple extends ConfigLoader {
	//
	// Protected methods.
	/**
	 * This method returns the list of config file paths to load.
	 *
	 * @return string[] Returns a list of absolute paths.
	 */
	protected function paths() {
		$out = [];

		$aux = Paths::Instance()->configPath($this->_config->name(), Paths::ExtensionJSON);
		if($aux) {
			$out[] = $aux;
		}

		return $out;
	}
}
