<?php

class KnownDebugsController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = true;
	//
	// Protected methods.
	protected function basicRun() {
		$knownDebugs = array(
			"debugwithoutcache" => "",
			"debugresetcache" => "Avoids current cache an regenerates it.",
			"debugnolang" => "Disables all translations and prints keys.",
			"debugnolayout" => "Disables current layout if any.",
			"debugmemcached" => "Prompts in HTML comments cashe keys used by memcached."
		);
		ksort($knownDebugs);
		$this->assign("knowndebugs", $knownDebugs);

		return true;
	}
}
