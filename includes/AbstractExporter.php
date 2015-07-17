<?php

/**
 * @file AbstractExporter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class AbstractExporter
 * @abstract
 */
abstract class AbstractExporter {
	//
	// Protected properties.
	/**
	 * @var mixed[string] List of values to use inside templates.
	 */
	protected $_assignments = array();
	/**
	 * @var mixed[] List of errors found while processing.
	 */
	protected $_errors = array();
	/**
	 * @var mixed[string] Last error found while processing.
	 */
	protected $_lastError = false;
	/**
	 * @var mixed[string] Last processing results.
	 */
	protected $_lastRun = false;
	/**
	 * @var boolean Current status for this controller/service.
	 */
	protected $_status = true;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param string $actionName internal identifier for the current
	 * action/service being represented.
	 */
	public function __construct($actionName = false) {
		//
		// Global dependencies.
		global $Defaults;
		//
		// Checking requested format.
		if(isset($this->params->get->format) && in_array($this->params->get->format, array_keys($Defaults[GC_DEFAULTS_FORMATS]))) {
			$this->_format = $this->params->get->format;
		} else {
			//
			// In case no format was requested or the requested one
			// is wrong, 'basic' is used.
			$this->_format = GC_VIEW_FORMAT_BASIC;
		}
		//
		// Checking the current format.
		if(isset($Defaults[GC_DEFAULTS_FORMATS][$this->_format])) {
			$this->_viewAdapter = new $Defaults[GC_DEFAULTS_FORMATS][$this->_format]();
		} else {
			throw new Exception("There's no configuration for format '{$this->_format}'");
		}
	}
	/**
	 * This magic methods enables the use of MagicProp and also to obtain
	 * assign values when the first one fails.
	 *
	 * @param string $prop Requested property.
	 * @return mixed Returns the recueste property either from MagicProp or
	 * from it's assignments.
	 */
	public function __get($prop) {
		//
		// Default values.
		$out = false;
		//
		// MagicProp exception control.
		try {
			$out = MagicProp::Instance()->{$prop};
		} catch(MagicPropException $ex) {
			//
			// If no MagicProp was found it try to obtain an assigned
			// value.
			$out = $this->get($prop);
		}
		//
		// Returning found value, if any.
		return $out;
	}
	//
	// Public methods.
	/**
	 * Assigns a value to a key for later use inside templates.
	 *
	 * @param string $key Key to identify the assignment.
	 * @param mixed $data Assigned value.
	 */
	public function assign($key, $data) {
		$this->_assignments[$key] = $data;
	}
	/**
	 * This methods allows to obtain a full list of current assignments.
	 *
	 * @return mixed[string] List of assignemnts.
	 */
	public function assignments() {
		return $this->_assignments;
	}
	/**
	 * Provides access to all errors found while processing.
	 *
	 * @return mixed[] List of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method allows to get an assigned value.
	 *
	 * @param string $key Key for the value to look for.
	 * @return mixed Return the found value or false when nothing is found.
	 */
	public function get($key) {
		return $this->isAssigned($key) ? $this->_assignments[$key] : false;
	}
	/**
	 * Checks if certian key is assigned.
	 *
	 * @param string $key Key to check.
	 * @return boolean Returns true when it is present.
	 */
	public function isAssigned($key) {
		return isset($this->_assignments[$key]);
	}
	/**
	 * Provides access to the last error found while processing.
	 *
	 * @return mixed[] List of errors.
	 */
	public function lastError() {
		return $this->_lastError;
	}
	/**
	 * Provides access to the last processing results.
	 *
	 * @return mixed[string] Processing results.
	 */
	public function lastRun() {
		return $this->_lastRun;
	}
	/**
	 * This method provides a way to massively assign values.
	 * When a value is already present it is overwriten.
	 *
	 * @param mixed[string] $list List of values to assign.
	 * @return mixed[string] Returns the full list of assignments.
	 */
	public function massiveAssign($list) {
		$this->_assignments = array_merge($this->assignments(), $list);
		return $this->assignments();
	}
	/**
	 * @abstract
	 * This is the method where the processing starts. Every child of this
	 * class must implement this method and perform a full execution.
	 *
	 * @return boolean Returns true if the execution had no errors.
	 */
	abstract public function run();
	/**
	 * Allows to know the current status of this object.
	 *
	 * @return boolean Current status.
	 */
	public function status() {
		return $this->_status;
	}
	//
	// Protected methods.
	/**
	 * This method generates a list of default assignments useful for any
	 * controller/service.
	 */
	protected function autoAssigns() {
		
	}
	/**
	 * This method allows to set an internal error.
	 *
	 * @param string $code Error code to be set.
	 * @param string $message Message to be attached to this error.
	 */
	protected function setError($code, $message) {
		//
		// Obtaining back trace information.
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		//
		// Where was it called?
		$callingLine = array_shift($trace);
		//
		// Who called it?
		$callerLine = array_shift($trace);
		//
		// Current error structure and data.
		$error = array(
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_LOCATION => array(
				GC_AFIELD_METHOD => (isset($callerLine['class']) ? "{$callerLine['class']}::" : '')."{$callerLine['function']}()",
				GC_AFIELD_FILE => $callingLine['file'],
				GC_AFIELD_LINE => $callingLine['line']
			)
		);
		//
		// Adding this error to the list.
		$this->_errors[] = $error;
		//
		// Setting this error as the last reported.
		$this->_lastError = $error;
		//
		// If at this point the status is ok and the error code is not,
		// the current status goes false.
		if($this->_status && $code != HTTPERROR_OK) {
			$this->_status = false;
		}
	}
}
