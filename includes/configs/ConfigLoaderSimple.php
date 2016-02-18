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
	public function paths($seed) {
		$out = array();

		$aux = Paths::Instance()->configPath($seed, Paths::ExtensionJSON);
		if($aux) {
			$out[] = $aux;
		}

		return $out;
	}
}
