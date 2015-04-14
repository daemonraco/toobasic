<?php

class KnownDebugsController extends TooBasic\Controller {
	//
	// Protected properties
	protected $_cached = false; //true;
	//
	// Protected methods.
	protected function basicRun() {
		$knownDebugs = array(
			"debugwithoutcache" => "",
			"debugresetcache" => "",
			"debugnolang" => "Disables all translations and prints keys.",
			"debugnolayout" => "Disables current layout if any."
		);
		ksort($knownDebugs);
		$this->assign("knowndebugs", $knownDebugs);

		return true;
	}
}
