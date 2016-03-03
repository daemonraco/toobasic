<?php

/**
 * @class GithubresultService
 *
 * Accessible at '?service=githubresult'
 */
class GithubresultService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::ExpirationSizeLarge;
	//
	// Protected methods.
	protected function basicRun() {
		$this->assign('toobasic', json_decode(file_get_contents(__DIR__.'/githubresult.json')));

		return $this->status();
	}
	protected function init() {
		parent::init();
	}
}
