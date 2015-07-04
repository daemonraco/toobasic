<?php

class KnownDebugsController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = \TooBasic\CacheAdapter::ExpirationSizeLarge;
	protected $_layout = false;
	//
	// Protected methods.
	protected function basicRun() {
		$file = $this->paths->configPath("known_debugs", \TooBasic\Paths::ExtensionJSON);
		$json = json_decode(file_get_contents($file), true);
		ksort($json["debugs"]);
		$this->assign("knowndebugs", $json["debugs"]);

		return true;
	}
}
