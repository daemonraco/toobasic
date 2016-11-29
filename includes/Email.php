<?php

/**
 * @file Email.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

abstract class Email extends AbstractExporter {
	//
	// Protected properties.
	/**
	 * @var \TooBasic\EmailPayload Configuration provided by the emails
	 * manager.
	 */
	protected $_payload = false;
	/**
	 * @var boolean This flag indicates if it's set to be a simulation or the
	 * real deal.
	 */
	protected $_isSimulation = false;
	/**
	 * @var string Name of the layout current controller uses. 'false' means
	 * no layout and 'null' means default layout.
	 */
	protected $_layout = null;
	/**
	 * @var mixed[string] Assignments made for snippets.
	 */
	protected $_snippetAssignments = [];
	/**
	 * @var string Current view template to render for this contorller.
	 */
	protected $_viewName = false;
	//
	// Magic methods.
	/**
	 * Class constructor
	 *
	 * @param \TooBasic\EmailPayload $emailPayload Payload to use when
	 * running.
	 */
	public function __construct($emailPayload) {
		parent::__construct($emailPayload->name());
		//
		// Saving a pointer to the given payload.
		$this->_payload = $emailPayload;
		//
		// Global requirements.
		global $Defaults;
		//
		// Picking a name for this controller's view. It could be
		// overriden by a controller allowing flex views.
		$this->_viewName = $this->_payload->name();
		//
		// It doesn't matter what it's set for the current class, there
		// are rules first.
		if(isset($this->params->debugnoemaillayout)) {
			//
			// Removing layout setting.
			$this->_layout = false;
		} elseif(isset($this->params->get->elayout)) {
			//
			// Using forced layout.
			$this->_layout = $this->params->get->elayout;
		} elseif($this->_layout === null) {
			//
			// If nothing is set, then the default is used.
			//
			// @note: NULL means there's no setting for this
			// controllers layout. FALSE means this controller uses
			// no layout.
			$this->_layout = $Defaults[GC_DEFAULTS_EMAIL_LAYOUT];
		}
	}
	//
	// Public methods.
	/**
	 * Allows to now if this emails is runing as a simulation.
	 *
	 * @return bool Rreturns when it is.
	 */
	public function isSimulation() {
		return $this->_isSimulation;
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
	public function payload() {
		return $this->_payload;
	}
	public function run() {
		//
		// When this method starts, 'status' is considered to be ok.
		$this->_status = true;
		//
		// Non simulated executions require a valid payload.
		if(!$this->isSimulation() && !$this->_payload->isValid()) {
			$this->setError(HTTPERROR_INTERNAL_SERVER_ERROR, 'Email payload strucutre is not valid');
		}
		//
		// Computing.
		if($this->_status) {
			$this->autoAssigns();

			if(!$this->isSimulation()) {
				//
				// Triggering the real execution.
				$this->_status = $this->basicRun();
			} else {
				$this->_status = $this->simulation();
			}
			//
			// Genering the last execution structure.
			$this->_lastRun = [
				GC_AFIELD_STATUS => $this->_status,
				GC_AFIELD_ASSIGNMENTS => $this->_assignments,
				GC_AFIELD_ERRORS => $this->_errors,
				GC_AFIELD_LASTERROR => $this->_lastError
			];
		}
		//
		// Rendering.
		if($this->_status) {
			$this->_lastRun[GC_AFIELD_RENDER] = false;
			//
			// Rendering and obtaining results @{
			$this->_viewAdapter->autoAssigns();
			$this->_lastRun[GC_AFIELD_RENDER] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("email/{$this->_viewName}.".Paths::ExtensionTemplate));
			// @}
		}

		return $this->status();
	}
	public function setSimulation($isSimulation) {
		$this->_isSimulation = $isSimulation;
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
	 * Allows to set a view name for this controller.
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
				$viewAdapter = \TooBasic\Adapter::Factory($Defaults[GC_DEFAULTS_VIEW_ADAPTER]);
				//
				// Rendering using the specified list of
				// assignments.
				$output = $viewAdapter->render($snippetDataSet ? $this->_snippetAssignments[$snippetDataSet] : $this->assignments(), $path);
			}
		}

		return (string)$output;
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
		// Current view name.
		$this->assign('view', $this->_viewName);
		//
		// Translation object
		$this->assign('tr', $this->translate);
		//
		// Controllers exported methods.
		$this->assign('ctrl', new EmailExports($this));
	}
	abstract protected function basicRun();
	protected function genericBasicRun() {
		$this->massiveAssign($this->_payload->data());
	}
	abstract protected function simulation();
}
