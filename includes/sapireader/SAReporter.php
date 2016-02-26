<?php

/**
 * @file SAReporter.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases
use TooBasic\Managers\SApiManager;
use TooBasic\Paths;

/**
 * @class SAReporterException
 * This type of exception is thrown whenever a error occurs with Simple API
 * Reporter.
 */
class SAReporterException extends Exception {
	
}

/**
 * @class SAReporter
 * This class holds the logic to access, change and also render Simple API
 * Reports.
 */
class SAReporter extends Singleton {
	//
	// Protected properties.
	/**
	 * @var \stdClass[string] List of loaded configurations.
	 */
	protected $_reports = [];
	//
	// Public methods.
	/**
	 * This method renders a report based on an API and returns it as a HTML
	 * table code.
	 *
	 * @param string $report Name of the report to be rendered.
	 * @param string $renderType Name of the mechanism to use when rendering. FALSE
	 * means default.
	 * @return string Returns a HTML piece of code.
	 * @throws \TooBasic\SAReporterException
	 */
	public function sareport($report, $renderType = false) {
		//
		// Default values.
		$out = '';
		//
		// Global dependencies.
		global $SApiReader;
		//
		// Loading requested report definition.
		$this->loadReport($report);
		//
		// Shortcut.
		$conf = $this->_reports[$report];
		//
		// Loading the associated API reader.
		$api = SApiManager::Instance()->{$conf->api};
		//
		// Requesting information to build the report.
		$results = $api->call($conf->service, $conf->params);
		//
		// Checking if a specific type of render should be used.
		if($renderType === false) {
			$renderType = $conf->type;
		}
		//
		// Checking rendering type.
		if(!isset($SApiReader[GC_SAREPORT_TYPES][$renderType])) {
			throw new SAReporterException("Unkwnon report render type '{$renderType}'.");
		}
		//
		// Rendering.
		$renderClass = $SApiReader[GC_SAREPORT_TYPES][$renderType];
		$render = new $renderClass($conf);
		$out.= $render->render($results);

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method check the certain report configuration and ands default
	 * values.
	 *
	 * @param string $report Report name.
	 * @throws \TooBasic\SAReporterException
	 */
	protected function checkReportConf($report) {
		//
		// Shortcut.
		$conf = $this->_reports[$report];
		//
		// Checking and setting a default report type.
		if(!isset($conf->type)) {
			$conf->type = GC_SAREPORT_TYPE_BASIC;
		}
		//
		// Checking each column definition.
		foreach($conf->columns as $column) {
			//
			// Checking and setting a default column type.
			if(!isset($column->type)) {
				$column->type = GC_SAREPORT_COLUMNTYPE_TEXT;
			}
			//
			// Checking and setting a default list of excluded values
			// for this column.
			if(!isset($column->exclude)) {
				$column->exclude = [];
			}
		}
		//
		// Checking and setting a default list of exceptions.
		if(!isset($conf->exceptions)) {
			$conf->exceptions = [];
		}
	}
	/**
	 * This method loads a report configuration, triggers checks on it and
	 * avoids multiple loads.
	 *
	 * @param string $report Report name.
	 * @throws \TooBasic\SAReporterException
	 */
	protected function loadReport($report) {
		//
		// Avoiding multiple loads.
		if(!isset($this->_reports[$report])) {
			//
			// Global dependencies.
			global $Paths;
			//
			// Looking for the right file path.
			$path = Paths::Instance()->customPaths($Paths[GC_PATHS_SAPIREPORTS], $report, Paths::ExtensionJSON);
			//
			// Checking path.
			if($path) {
				//
				// Loading JSON specification.
				$json = json_decode(file_get_contents($path));
				//
				// Checking the loaded object.
				if($json) {
					//
					// Saving the configuration for further
					// use.
					$this->_reports[$report] = $json;
					//
					// Checking configuration.
					$this->checkReportConf($report);
				} else {
					throw new SAReporterException("Path '{$path}' is not a valid JSON (".json_last_error_msg().').');
				}
			} else {
				throw new SAReporterException("Unable to find a definition for report '{$report}'.");
			}
		}
	}
}
