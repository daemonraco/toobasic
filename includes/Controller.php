<?php

/**
 * @file Controller.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

use TooBasic\Managers\ActionsManager as ActionsManager;

/**
 * @class Controller
 * @abstract
 *
 * This class represents a general controller with all its logic for checks,
 * execution and display
 */
abstract class Controller extends Exporter {
	//
	// Protected properties.
	/**
	 * @var string[] List of assignment keys that cannot be stored in cache.
	 */
	protected $_cacheNoise = array(
		'tr',
		'ctrl'
	);
	/**
	 * @var string Name of the layout current controller uses. 'false' means
	 * no layout and 'null' means default layout.
	 */
	protected $_layout = null;
	/**
	 * @var mixed[string] Assignments made for snippets.
	 */
	protected $_snippetAssignments = array();
	/**
	 * @var string Current view template to render for this contorller.
	 */
	protected $_viewName = false;
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
		// Global requirements.
		global $Defaults;
		global $ActionName;
		//
		// Picking a name for this controller's view. It could be
		// overriden by a controller allowing flex views.
		$this->_viewName = $actionName ? $actionName : $ActionName;
		//
		// Initializing parent class
		parent::__construct($actionName);
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
			$this->_layout = $Defaults[GC_DEFAULTS_LAYOUT];
		}
	}
	//
	// Public methods.
	/**
	 * Checks for conditions that may require this controller to stop and
	 * redirect to another.
	 *
	 * @return string Returns the name of a redirection configuration.
	 */
	public function checkRedirectors() {
		//
		// By default, a controller never redirects.
		return false;
	}
	/**
	 * This is an exported method that can be used inside templates. It
	 * takes an action name an returns its rendered result.
	 *
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	public function insert($actionName) {
		$lastRun = ActionsManager::ExecuteAction($actionName);
		return $lastRun[GC_AFIELD_RENDER];
	}
	/**
	 * Allows to know which layout is to be used when this controller is
	 * render.
	 *
	 * @return string Layout name. 'false' when no layout and null when it
	 * must be default.
	 */
	public function layout() {
		return $this->_layout;
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
		// When this method starts, 'status' is considered to be ok.
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
			if($this->_cached && !isset($this->params->debugresetcache)) {
				$this->_lastRun = $this->cache->get($prefixComputing, $key, $this->_cached);
			} else {
				$this->_lastRun = false;
			}
			//
			// Checking if there were a previous execution or a
			// debug parameter resetting the cache.
			if($this->_lastRun) {
				//
				// Loading data from the previous execution.
				$this->_assignments = $this->_lastRun[GC_AFIELD_ASSIGNMENTS];
				$this->_status = $this->_lastRun[GC_AFIELD_STATUS];
				$this->_errors = $this->_lastRun[GC_AFIELD_ERRORS];
				$this->_lastError = $this->_lastRun[GC_AFIELD_LASTERROR];
				//
				// Because there are some auto-assignments not
				// present in cache, they are reinforced.
				$this->autoAssigns();
			} else {
				//
				// Triggering the real execution.
				$this->_status = $this->dryRun();
				//
				// Genering the last execution structure.
				$this->_lastRun = array(
					GC_AFIELD_STATUS => $this->_status,
					GC_AFIELD_ASSIGNMENTS => $this->_assignments,
					GC_AFIELD_ERRORS => $this->_errors,
					GC_AFIELD_LASTERROR => $this->_lastError
				);
				//
				// Removing cache noise.
				foreach($this->_cacheNoise as $noise) {
					if(isset($this->_lastRun[GC_AFIELD_ASSIGNMENTS][$noise])) {
						unset($this->_lastRun[GC_AFIELD_ASSIGNMENTS][$noise]);
					}
				}
				//
				// Storing a cache entry if it's active.
				if($this->_cached) {
					$this->cache->save($prefixComputing, $key, $this->_lastRun, $this->_cached);
				}
				//
				// Because there are some auto-assignments not
				// present in cache, they are reinforced.
				$this->autoAssigns();
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
			if($this->_cached && !isset($this->params->debugresetcache)) {
				$dataBlock = $this->cache->get($prefixRender, $key, $this->_cached);
				$this->_lastRun[GC_AFIELD_HEADERS] = $dataBlock[GC_AFIELD_HEADERS];
				$this->_lastRun[GC_AFIELD_RENDER] = $dataBlock[GC_AFIELD_RENDER];
			} else {
				$this->_lastRun[GC_AFIELD_HEADERS] = array();
				$this->_lastRun[GC_AFIELD_RENDER] = false;
			}
			//
			// Checking if there were a previous execution or a
			// debug parameter resetting the cache.
			if(!$this->_lastRun[GC_AFIELD_RENDER]) {
				//
				// Rendering and obtaining results @{
				$this->_viewAdapter->autoAssigns();
				$this->_lastRun[GC_AFIELD_HEADERS] = $this->_viewAdapter->headers();
				$this->_lastRun[GC_AFIELD_RENDER] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("{$this->_mode}/{$this->_viewName}.".Paths::ExtensionTemplate));
				// @}
				//
				// Storing a cache entry if it's active.
				if($this->_cached) {
					$this->cache->save($prefixRender, $key, array(
						GC_AFIELD_HEADERS => $this->_lastRun[GC_AFIELD_HEADERS],
						GC_AFIELD_RENDER => $this->_lastRun[GC_AFIELD_RENDER]
						), $this->_cached);
				}
			}
		}

		return $this->status();
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
	 * Allows to set view name for this controller.
	 *
	 * @param string $viewName Name to be set.
	 */
	public function setViewName($viewName) {
		$this->_viewName = $viewName;
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
		$output = '';
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
			if($this->_format == GC_VIEW_FORMAT_BASIC) {
				global $Defaults;
				//
				// Generating a view adapter to render the
				// snippet.
				$viewAdapter = \TooBasic\Adapters\Adapter::Factory($Defaults[GC_DEFAULTS_VIEW_ADAPTER]);
				//
				// Rendering using the specified list of
				// assignments.
				$output = $viewAdapter->render($snippetDataSet ? $this->_snippetAssignments[$snippetDataSet] : $this->assignments(), $path);
			}
		}

		return (string) $output;
	}
	/**
	 * Allows to access the view name of this controller.
	 *
	 * @return string Name of current view.
	 */
	public function viewName() {
		return $this->_viewName;
	}
	//
	// Protected methods.
	/**
	 * This method adds some default values to any controller assignments.
	 */
	protected function autoAssigns() {
		//
		// Adding parent's default assignments.
		parent::autoAssigns();
		//
		// Current format.
		$this->assign('format', $this->_format);
		//
		// Current mode.
		$this->assign('mode', $this->_mode);
		//
		// Current view name.
		$this->assign('view', $this->_viewName);
		//
		// Translation object
		$this->assign('tr', $this->translate);
		//
		// Controllers exported methods.
		$this->assign('ctrl', new ControllerExports($this));
	}
	/**
	 * Controllers has a specific method to generate cache prefixes in order
	 * to include skins.
	 *
	 * @global string $SkinName
	 * @param string $extra
	 * @return string
	 */
	protected function cachePrefix($extra = '') {
		//
		// Global dependecies.
		global $SkinName;
		//
		// Default prefix.
		$skinPrefix = '';
		//
		// Generating a prefix containing the skin's name, unless there's
		// no skin set.
		if($SkinName) {
			$skinPrefix = "_sk_{$SkinName}";
		}
		//
		// Resulting prefix is a combination of the current skin name and
		// the prefix built by this class parent.
		return parent::cachePrefix($extra).$skinPrefix;
	}
}
