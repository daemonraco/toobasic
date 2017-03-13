<?php

class KnownDebugsController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		/** @todo use Configs class */
		$file = $this->paths->configPath("known_debugs", \TooBasic\Paths::EXTENSION_JSON);
		$json = json_decode(file_get_contents($file), true);
		ksort($json["debugs"]);
		$this->assign("knowndebugs", $json["debugs"]);

		return true;
	}
}
