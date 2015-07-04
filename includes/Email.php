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
	 * @var \TooBasic\EmailPayload @todo doc
	 */
	protected $_payload = false;
	/**
	 * @var boolean @todo doc
	 */
	protected $_isSimulation = false;
	/**
	 * @var string Name of the layout current controller uses. 'false' means
	 * no layout and 'null' means default layout.
	 */
	protected $_layout = null;
	/**
	 * @var string Current view template to render for this contorller.
	 */
	protected $_viewName = false;
	//
	// Magic methods.
	/**
	 * Class constructor
	 * 
	 * @param string[string] $emailPayload 
	 */
	public function __construct($emailPayload) {
		parent::__construct($emailPayload->name());

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
			$this->_lastRun = array(
				'status' => $this->_status,
				'assignments' => $this->_assignments,
				'errors' => $this->_errors,
				'lasterror' => $this->_lastError
			);
		}
		//
		// Rendering.
		if($this->_status) {
			$this->_lastRun['render'] = false;
			//
			// Rendering and obtaining results @{
			$this->_viewAdapter->autoAssigns();
			$this->_lastRun['render'] = $this->_viewAdapter->render($this->assignments(), Sanitizer::DirPath("email/{$this->_viewName}.".Paths::ExtensionTemplate));
			// @}
		}

		return $this->status();
	}
	public function setSimulation($isSimulation) {
		$this->_isSimulation = $isSimulation;
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
