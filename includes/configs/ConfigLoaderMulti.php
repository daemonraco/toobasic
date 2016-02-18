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
	public function paths($seed) {
		return Paths::Instance()->configPath($seed, Paths::ExtensionJSON, true);
	}
}
