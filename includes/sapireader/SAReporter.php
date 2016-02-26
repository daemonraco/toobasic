<?php

namespace TooBasic;

use TooBasic\Managers\SApiManager;
use TooBasic\Paths;

class SAReporterException extends Exception {
	
}

class SAReporter extends Singleton {
	protected $_reports = [];
	/**
	 * 
	 * @param type $conf
	 * @return string
	 * @throws \TooBasic\SAReporterException
	 */
	public function sareport($report, $renderType = false) {
		$out = '';

		$this->loadReport($report);
		$conf = $this->_reports[$report];

		$api = SApiManager::Instance()->{$conf->api};
		$results = $api->call($conf->service, $conf->params);
		//
		// Global dependencies.
		global $SApiReader;
		if($renderType === false) {
			$renderType = $conf->type;
		}
		if(!isset($SApiReader[GC_SAREPORT_TYPES][$renderType])) {
			throw new SAReporterException("Unkwnon report render type '{$renderType}'.");
		}
		$renderClass = $SApiReader[GC_SAREPORT_TYPES][$renderType];
		$render = new $renderClass($conf);
		$out.= $render->render($results);

		return $out;
	}
	/**
	 * 
	 * @param type $report
	 * @throws \TooBasic\SAReporterException
	 */
	protected function checkReportConf($report) {
		$conf = $this->_reports[$report];
		if(!isset($conf->type)) {
			$conf->type = GC_SAREPORT_TYPE_BASIC;
		}

		foreach($conf->columns as $column) {
			if(!isset($column->type)) {
				$column->type = GC_SAREPORT_COLUMNTYPE_TEXT;
			}
			if(!isset($column->exclude)) {
				$column->exclude = [];
			}
		}

		if(!isset($conf->exceptions)) {
			$conf->exceptions = [];
		}
	}
	/**
	 * 
	 * @param type $report
	 * @throws \TooBasic\SAReporterException
	 */
	protected function loadReport($report) {
		if(!isset($this->_reports[$report])) {
			//
			// Global dependencies.
			global $Paths;

			$path = Paths::Instance()->customPaths($Paths[GC_PATHS_SAPIREPORTS], $report, Paths::ExtensionJSON);

			if($path) {
				$json = json_decode(file_get_contents($path));
				if($json) {
					$this->_reports[$report] = $json;
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
