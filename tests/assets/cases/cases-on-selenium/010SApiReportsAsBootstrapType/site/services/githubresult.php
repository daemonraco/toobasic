<?php

/**
 * @class GithubresultService
 *
 * Accessible at '?service=githubresult'
 */
class GithubresultService extends \TooBasic\Service {
	//
	// Protected properties
	protected $_cached = \TooBasic\Adapters\Cache\Adapter::EXPIRATION_SIZE_LARGE;
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
