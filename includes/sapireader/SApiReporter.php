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
use TooBasic\Translate;

/**
 * @class SAReporterException
 * This type of exception is thrown whenever a error occurs with Simple API
 * Reporter.
 */
class SApiReportException extends Exception {
	
}

/**
 * @class SAReporter
 * This class holds the logic to access, change and also render Simple API
 * Reports.
 */
class SApiReporter extends Singleton {
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
	 * @throws \TooBasic\SApiReportException
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
		if(!isset($SApiReader[GC_SAPIREPORT_TYPES][$renderType])) {
			throw new SApiReportException("Unkwnon report render type '{$renderType}'.");
		}
		//
		// Filtering results.
		$list = $this->filterResults($report, $results);
		//
		// Rendering.
		$renderClass = $SApiReader[GC_SAPIREPORT_TYPES][$renderType];
		$render = new $renderClass($conf);
		$out.= $render->render($list);

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method check the certain report configuration and ands default
	 * values.
	 *
	 * @param string $report Report name.
	 * @throws \TooBasic\SApiReportException
	 */
	protected function checkReportConf($report) {
		//
		// Shortcut.
		$conf = $this->_reports[$report];
		//
		// Checking and setting a default report type.
		if(!isset($conf->type)) {
			$conf->type = GC_SAPIREPORT_TYPE_BASIC;
		}
		//
		// Lists of required field by column type and default values.
		$columnDefaults = [
			GC_SAPIREPORT_COLUMNTYPE_BUTTONLINK => [
				'attrs' => '+\stdClass',
				'exclude' => [],
				'label' => false,
				'label_field' => false,
				'link' => '+\stdClass'
			],
			GC_SAPIREPORT_COLUMNTYPE_CODE => [
				'attrs' => '+\stdClass',
				'exclude' => []
			],
			GC_SAPIREPORT_COLUMNTYPE_IMAGE => [
				'attrs' => '+\stdClass',
				'exclude' => [],
				'src' => '+\stdClass'
			],
			GC_SAPIREPORT_COLUMNTYPE_LINK => [
				'attrs' => '+\stdClass',
				'exclude' => [],
				'label' => false,
				'label_field' => false,
				'link' => '+\stdClass'
			],
			GC_SAPIREPORT_COLUMNTYPE_TEXT => [
				'attrs' => '+\stdClass',
				'exclude' => []
			]
		];
		//
		// List of accepted column types.
		$knownTypes = array_keys($columnDefaults);
		//
		// Checking each column definition.
		foreach($conf->columns as $column) {
			//
			// Checking and setting a default column type.
			if(!isset($column->type)) {
				$column->type = GC_SAPIREPORT_COLUMNTYPE_TEXT;
			}
			//
			// Checking type.
			if(!in_array($column->type, $knownTypes)) {
				throw new SApiReportException("Unknown column type '{$column->type}'.");
			}
			//
			// Checking and enforcing column definition fileds.
			$column = \TooBasic\objectCopyAndEnforce(array_keys($columnDefaults[$column->type]), new \stdClass(), $column, $columnDefaults[$column->type]);
		}
		//
		// Checking and setting a default list of exceptions.
		if(!isset($conf->exceptions)) {
			$conf->exceptions = [];
		}
	}
	/**
	 * @TODO doc
	 *
	 * @param type $report @TODO doc
	 * @param type $results @TODO doc
	 * @return type @TODO doc
	 * @throws \TooBasic\SApiReportException
	 */
	protected function filterResults($report, $results) {
		//
		// Default values.
		$list = false;
		//
		// Shortcuts.
		$conf = $this->_reports[$report];
		//
		// Getting a shortcut to the list of items inside into results.
		if($conf->listPath) {
			$list = self::GetPathValue($results, $conf->listPath);
		} else {
			$list = $results;
		}
		if(!is_array($list)) {
			throw new SApiReportException("Result's path '->{$conf->listPath}' doesn't point to a list.");
		}

		foreach($list as $itemKey => $item) {
			$exclude = false;

			foreach($conf->exceptions as $exception) {
				$path = self::GetPathCleaned($exception->path);
				$isset = self::GetPathIsset($item, $path);
				if($isset) {
					$value = self::GetPathValue($item, $path);
				} else {
					$value = false;
				}

				if(isset($exception->isset) && $isset == $exception->isset) {
					$exclude = true;
					break;
				}
				if(isset($exception->exclude) && $isset && in_array($value, $exception->exclude)) {
					$exclude = true;
					break;
				}
			}
			if(!$exclude) {
				foreach($conf->columns as $column) {
					$value = self::GetPathValue($item, $column->path);

					if(in_array($value, $column->exclude)) {
						$exclude = true;
						break;
					}
				}
			}
			if($exclude) {
				unset($list[$itemKey]);
				continue;
			}
		}

		return $list;
	}
	/**
	 * This method loads a report configuration, triggers checks on it and
	 * avoids multiple loads.
	 *
	 * @param string $report Report name.
	 * @throws \TooBasic\SApiReportException
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
					throw new SApiReportException("Path '{$path}' is not a valid JSON (".json_last_error_msg().').');
				}
			} else {
				throw new SApiReportException("Unable to find a definition for report '{$report}'.");
			}
		}
	}
	//
	// Public class methods.
	/**
	 * @TODO doc
	 *
	 * @param type $path @TODO doc
	 * @return type @TODO doc
	 */
	public static function GetPathCleaned($path) {
		return implode('->', explode('/', $path));
	}
	/**
	 * @TODO doc
	 *
	 * @param type $item @TODO doc
	 * @param type $path @TODO doc
	 * @return type @TODO doc
	 */
	public static function GetPathIsset($item, $path) {
		$path = self::GetPathCleaned($path);
		eval("\$out=isset(\$item->{$path});");
		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $item @TODO doc
	 * @param type $path @TODO doc
	 * @return type @TODO doc
	 */
	public static function GetPathValue($item, $path) {
		$path = self::GetPathCleaned($path);
		eval("\$out=isset(\$item->{$path})?\$item->{$path}:false;");
		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @staticvar boolean $tr @TODO doc
	 * @param type $label @TODO doc
	 * @return type @TODO doc
	 */
	public static function TranslateLabel($label) {
		static $tr = false;
		if($tr === false) {
			$tr = Translate::Instance();
		}

		$out = $label;
		if(is_string($label)) {
			$matches = false;
			if(preg_match('~@(?P<key>(.*))~', $label, $matches)) {
				$out = $tr->{$matches['key']};
			}
		}

		return $out;
	}
}
