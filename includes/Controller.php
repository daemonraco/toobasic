<?php

namespace TooBasic;

abstract class Controller extends Exporter {
	//
	// Protected properties.
	protected $_layout = null;
	protected $_snippetAssignments = array();
	//
	// Magic methods.
	public function __construct($actionName = false) {
		parent::__construct($actionName);

		global $Defaults;
		//
		// It doesn't matter what it's set for the current class, there
		// are rules first.
		if(isset($this->_params->debugnolayout)) {
			//
			// Removing layout setting.
			$this->_layout = false;
		} elseif(isset($_REQUEST["layout"])) {
			//
			// Using forced layout.
			$this->_layout = $_REQUEST["layout"];
		} elseif($this->_layout === null) {
			//
			// If nothing is set, then the default is used.
			//
			// @note: NULL means there's no setting for this controllers layout. FALSE means this controller uses no layout.
			$this->_layout = $Defaults["layout"];
		}
	}
	//
	// Public methods.
	public function insert($actionName) {
		$lastRun = ActionsManager::ExecuteAction($actionName);
		return $lastRun["render"];
	}
	public function layout() {
		return $this->_layout;
	}
	public function setSnippetDataSet($key, $value = null) {
		if($value === null) {
			unset($this->_snippetAssignments[$key]);
		} else {
			$this->_snippetAssignments[$key] = $value;
		}
	}
	public function snippet($snippetName, $snippetDataSet = false) {
		$output = "";

		$path = Paths::Instance()->snippetPath($snippetName);
		if($path) {
			if($snippetDataSet == false || !isset($this->_snippetAssignments[$snippetDataSet])) {
				$snippetDataSet = false;
			}

			if($this->_format == self::FormatBasic) {
				global $Defaults;

				$viewAdapter = new $Defaults["view-adapter"]();
				$output = $viewAdapter->render($snippetDataSet ? $this->_snippetAssignments[$snippetDataSet] : $this->assignments(), $path);
			}
		}

		return (string) $output;
	}
	/**
	 * @todo doc
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		$this->_status = true;

		$this->checkParams();

		$key = $this->cacheKey();
		//
		// Computing cache.
		if($this->_status) {
			$prefixComputing = $this->cachePrefix(Exporter::PrefixComputing);

			if($this->_cached) {
				$this->_lastRun = $this->cache->get($prefixComputing, $key);
			} else {
				$this->_lastRun = false;
			}

			if($this->_lastRun && !isset($this->_params->debugresetcache)) {
				$this->_assignments = $this->_lastRun["assignments"];
				$this->_status = $this->_lastRun["status"];
				$this->_errors = $this->_lastRun["errors"];
				$this->_lastError = $this->_lastRun["lasterror"];
			} else {
				$this->autoAssigns();
				$this->_status = $this->dryRun();
				$this->_lastRun = array(
					"status" => $this->_status,
					"assignments" => $this->_assignments,
					"errors" => $this->_errors,
					"lasterror" => $this->_lastError
				);

				if($this->_cached) {
					$this->cache->save($prefixComputing, $key, $this->_lastRun);
				}
			}
		}
		//
		// Render cache.
		if($this->_status) {
			$prefixRender = $this->cachePrefix(Exporter::PrefixRender);

			if($this->_cached) {
				$dataBlock = $this->cache->get($prefixRender, $key);
				$this->_lastRun["headers"] = $dataBlock["headers"];
				$this->_lastRun["render"] = $dataBlock["render"];
			} else {
				$this->_lastRun["headers"] = array();
				$this->_lastRun["render"] = false;
			}

			if(!$this->_lastRun["render"] || isset($this->_params->debugresetcache)) {
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun["headers"] = $this->_viewAdapter->headers();
				$this->_lastRun["render"] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_name}.".Paths::ExtensionTemplate));

				if($this->_cached) {
					$this->cache->save($prefixRender, $key, array(
						"headers" => $this->_lastRun["headers"],
						"render" => $this->_lastRun["render"]
					));
				}
			}
		}

		return $this->status();
	}
	//
	// Protected methods.
	protected function autoAssigns() {
		parent::autoAssigns();

		$this->assign("format", $this->_format);
		$this->assign("mode", $this->_mode);
		$this->assign("tr", $this->translate);
		$this->assign("ctrl", $this);
	}
}
