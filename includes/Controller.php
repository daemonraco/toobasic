<?php

namespace TooBasic;

/**
 * @abstract
 * @class Controller
 * 
 * This class represents a general controller with all its logic for checks,
 * execution and display
 */
abstract class Controller extends Exporter {
	//
	// Protected properties.
	/**
	 * @var string Name of the layout current controller uses. "false" means
	 * no layout and "null" means default layout.
	 */
	protected $_layout = null;
	/**
	 * @var mixed[string] Assignments made for snippets.
	 */
	protected $_snippetAssignments = array();
	//
	// Magic methods.
	/**
	 * Class constructor
	 * 
	 * @param string $actionName An indentifier name for this controller, by
	 * default it's currect action's name.
	 */
	public function __construct($actionName = false) {
		//
		// Initializing parent class
		parent::__construct($actionName);
		//
		// Global requirements.
		global $Defaults;
		//
		// It doesn't matter what it's set for the current class, there
		// are rules first.
		if(isset($this->params->debugnolayout)) {
			//
			// Removing layout setting.
			$this->_layout = false;
		} elseif(isset($this->params->get->layout)) {
			//
			// Using forced layout.
			$this->_layout = $this->params->get->layout;
		} elseif($this->_layout === null) {
			//
			// If nothing is set, then the default is used.
			//
			// @note: NULL means there's no setting for this
			// controllers layout. FALSE means this controller uses
			// no layout.
			$this->_layout = $Defaults["layout"];
		}
	}
	//
	// Public methods.
	/**
	 * This is an exported method that can be used inside templates. It
	 * takes an action name an returns its rendered result.
	 * 
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	public function insert($actionName) {
		$lastRun = ActionsManager::ExecuteAction($actionName);
		return $lastRun["render"];
	}
	/**
	 * Allows to know which layout is to be used when this controller is
	 * render.
	 * 
	 * @return string Layout name. 'false" when no layout and null when it
	 * must be default.
	 */
	public function layout() {
		return $this->_layout;
	}
	/**
	 * Allows to set a list of assignment to use when a snippet is called.
	 * 
	 * @param string $key List of assignments' name.
	 * @param mixed[string] $value Assignments. When null, it removes the
	 * entry.
	 */
	public function setSnippetDataSet($key, $value = null) {
		if($value === null) {
			unset($this->_snippetAssignments[$key]);
		} else {
			$this->_snippetAssignments[$key] = $value;
		}
	}
	/**
	 * This is an exported method that can be used inside templates. It
	 * takes an snippet name an returns its rendered result.
	 * 
	 * @param string $snippetName Name of the snippet to render.
	 * @param string $snippetDataSet List of assignments' name to use when
	 * rendering.
	 * @return string Result of rendering the snippet.
	 */
	public function snippet($snippetName, $snippetDataSet = false) {
		$output = "";
		//
		// Looking for the snippet.
		$path = $this->paths->snippetPath($snippetName);
		if($path) {
			if($snippetDataSet == false || !isset($this->_snippetAssignments[$snippetDataSet])) {
				$snippetDataSet = false;
			}
			//
			// Snippets are rendered only when when a basic format
			// is in used.
			if($this->_format == self::FormatBasic) {
				global $Defaults;
				//
				// Generating a view adapter to render the
				// snippet.
				$viewAdapter = new $Defaults["view-adapter"]();
				//
				// Rendering using the specified list of
				// assignments.
				$output = $viewAdapter->render($snippetDataSet ? $this->_snippetAssignments[$snippetDataSet] : $this->assignments(), $path);
			}
		}

		return (string) $output;
	}
	/**
	 * This is the main method that trigger the real excution and rendering
	 * of current controller.
	 * 	* Checks parameters.
	 * 	* Loads and save cache.
	 * 	* Controls errors.
	 * 	* Renders.
	 * 
	 * @return boolean Returns true if the execution had no errors.
	 */
	public function run() {
		//
		// When this method starts, "status" is considered to be ok.
		$this->_status = true;
		//
		// Checking parameters.
		$this->checkParams();
		//
		// Calculating a cache key for this execution.
		$key = $this->cacheKey();
		//
		// Computing.
		if($this->_status) {
			//
			// Generating a cache key prefix for the computing cache
			// entry.
			$prefixComputing = $this->cachePrefix(Exporter::PrefixComputing);
			//
			// If this controller uses cache, it tries to obtain the
			// previous execution.
			if($this->_cached) {
				$this->_lastRun = $this->cache->get($prefixComputing, $key, $this->_cached);
			} else {
				$this->_lastRun = false;
			}
			//
			// Checking if there were a previous execution or a
			// debug parameter resetting the cache.
			if($this->_lastRun && !isset($this->params->debugresetcache)) {
				//
				// Loading data from the previous execution.
				$this->_assignments = $this->_lastRun["assignments"];
				$this->_status = $this->_lastRun["status"];
				$this->_errors = $this->_lastRun["errors"];
				$this->_lastError = $this->_lastRun["lasterror"];
			} else {
				$this->autoAssigns();
				//
				// Triggering the real execution.
				$this->_status = $this->dryRun();
				//
				// Genering the last execution structure.
				$this->_lastRun = array(
					"status" => $this->_status,
					"assignments" => $this->_assignments,
					"errors" => $this->_errors,
					"lasterror" => $this->_lastError
				);
				//
				// Storing a cache entry if it's active.
				if($this->_cached) {
					$this->cache->save($prefixComputing, $key, $this->_lastRun, $this->_cached);
				}
			}
		}
		//
		// Rendering.
		if($this->_status) {
			//
			// Generating a cache key prefix for the rendering cache
			// entry.
			$prefixRender = $this->cachePrefix(Exporter::PrefixRender);
			//
			// If this controller uses cache, it tries to obtain the
			// previous execution.
			if($this->_cached) {
				$dataBlock = $this->cache->get($prefixRender, $key, $this->_cached);
				$this->_lastRun["headers"] = $dataBlock["headers"];
				$this->_lastRun["render"] = $dataBlock["render"];
			} else {
				$this->_lastRun["headers"] = array();
				$this->_lastRun["render"] = false;
			}
			//
			// Checking if there were a previous execution or a
			// debug parameter resetting the cache.
			if(!$this->_lastRun["render"] || isset($this->params->debugresetcache)) {
				//
				// Rendering and obtaining results @{
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun["headers"] = $this->_viewAdapter->headers();
				$this->_lastRun["render"] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_name}.".Paths::ExtensionTemplate));
				// @}
				//
				// Storing a cache entry if it's active.
				if($this->_cached) {
					$this->cache->save($prefixRender, $key, array(
						"headers" => $this->_lastRun["headers"],
						"render" => $this->_lastRun["render"]
					), $this->_cached);
				}
			}
		}

		return $this->status();
	}
	//
	// Protected methods.
	/**
	 * This method adds some default values to any controller assignments.
	 */
	protected function autoAssigns() {
		parent::autoAssigns();

		$this->assign("format", $this->_format);
		$this->assign("mode", $this->_mode);
		//
		// Translation object
		$this->assign("tr", $this->translate);
		//
		// Controllers exported methods.
		$this->assign("ctrl", new ControllerExports($this));
	}
	protected function cachePrefix($extra = "") {
		global $SkinName;

		$skinPrefix = '';
		if($SkinName) {
			$skinPrefix = "_sk_{$SkinName}";
		}

		return parent::cachePrefix($extra).$skinPrefix;
	}
}
