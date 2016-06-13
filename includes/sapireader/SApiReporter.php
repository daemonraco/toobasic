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
		// Loading the associated API reader.
		$api = SApiManager::Instance()->{$conf->api};
		//
		// Requesting information to build the report.
		$results = $api->call($conf->service, $conf->params);
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
		// Lists of required configuration fields and default values.
		$fields = [
			'attrs' => '+\stdClass',
			'exceptions' => [],
			'listPath' => '',
			'name' => $report,
			'params' => [],
			'type' => GC_SAPIREPORT_TYPE_BASIC
		];
		//
		// Checking and enforcing configuration.
		$this->_reports[$report] = \TooBasic\objectCopyAndEnforce(array_keys($fields), new \stdClass(), $this->_reports[$report], $fields);
		//
		// Shortcut.
		$conf = $this->_reports[$report];
		//
		// Checking required fields.
		foreach(['api', 'service', 'columns'] as $field) {
			if(!isset($conf->{$field})) {
				throw new SApiReportException("Report '{$report}' lacks the required field '{$field}'.");
			}
		}
		if(!is_array($conf->columns)) {
			throw new SApiReportException("Report '{$report}' field 'columns' is not a list.");
			if(!boolval(count($conf->columns))) {
				throw new SApiReportException("Report '{$report}' field 'columns' has no entries.");
			}
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
		// List of accepted column types.
		$requiredColumnFields = [
			'title',
			'path',
			'type'
		];
		//
		// Checking each column definition.
		foreach($conf->columns as $columnPosition => $column) {
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
			//
			// Checking required fields.
			foreach($requiredColumnFields as $field) {
				if(!isset($column->{$field})) {
					throw new SApiReportException(\TooBasic\ordinalToCardinal($columnPosition + 1)." column on report '{$report}' lacks the required field '{$field}'.");
				}
			}
		}
	}
	/**
	 * This method takes a full result of a Simple API Reader call and extract
	 * only the information that can be shown based on configuration.
	 *
	 * @param string $report Name of a report to use as configuration.
	 * @param \stdClass $results Full API result.
	 * @return \stdClass[] Returns a filtered list.
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
			if(!is_array($list)) {
				throw new SApiReportException("Result's path '->".self::GetPathCleaned($conf->listPath)."' doesn't point to a list.");
			}
		} else {
			$list = $results;
			if(!is_array($list)) {
				throw new SApiReportException("Result is not a list.");
			}
		}
		//
		// Checking each entry and excluding by configuration.
		foreach($list as $itemKey => $item) {
			$exclude = false;
			//
			// Checking each global exclusion for current entry.
			foreach($conf->exceptions as $exception) {
				//
				// Retieving values.
				$path = self::GetPathCleaned($exception->path);
				$isset = self::GetPathIsset($item, $path);
				if($isset) {
					$value = self::GetPathValue($item, $path);
				} else {
					$value = false;
				}
				//
				// Checking 'isset' exception.
				if(isset($exception->isset) && $isset == $exception->isset) {
					$exclude = true;
					break;
				}
				//
				// Checking excluded values.
				if(isset($exception->exclude) && $isset && in_array($value, $exception->exclude)) {
					$exclude = true;
					break;
				}
			}
			//
			// Checking value exclusions configured on each column.
			if(!$exclude) {
				foreach($conf->columns as $column) {
					$value = self::GetPathValue($item, $column->path);

					if(in_array($value, $column->exclude)) {
						$exclude = true;
						break;
					}
				}
			}
			//
			// If it's excluded, it's removed from the list.
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
					throw new SApiReaderException(Translate::Instance()->EX_JSON_invalid_file([
						'path' => $path,
						'errorcode' => json_last_error(),
						'error' => json_last_error_msg()
					]));
				}
			} else {
				throw new SApiReportException("Unable to find a definition for report '{$report}'.");
			}
		}
	}
	//
	// Public class methods.
	/**
	 * This class method takes a configured path and convert it into an
	 * evaluable object sub-path.
	 *
	 * @param string $path Path to be clean.
	 * @return string Returns a path like 'prop->subprop'.
	 */
	public static function GetPathCleaned($path) {
		return implode('->', explode('/', $path));
	}
	/**
	 * This class method checks if certain object path is set inside certain
	 * object.
	 *
	 * @param \stdClass $item Object to be analyzed.
	 * @param string $path Path to be clean.
	 * @return boolean Returns TRUE when it's set.
	 */
	public static function GetPathIsset($item, $path) {
		$path = self::GetPathCleaned($path);
		eval("\$out=isset(\$item->{$path});");
		return $out;
	}
	/**
	 * This class method retieves certain object path inside a given object.
	 *
	 * @param \stdClass $item Object to be analyzed.
	 * @param string $path Path to be clean.
	 * @return mixed Returns it's value or FALSE when it's not set.
	 */
	public static function GetPathValue($item, $path) {
		$path = self::GetPathCleaned($path);
		eval("\$out=isset(\$item->{$path})?\$item->{$path}:false;");
		return $out;
	}
	/**
	 * This mehtod takes a label string and checks if it's flagged to be
	 * translated (in other words, if it has an at-sign at the beginning.
	 *
	 * @param string $label Label to be analyzed.
	 * @return string Returns a clean label.
	 */
	public static function TranslateLabel($label) {
		//
		// Shortcut to avoid multiple singleton searches.
		static $tr = false;
		if($tr === false) {
			$tr = Translate::Instance();
		}
		//
		// Default values.
		$out = $label;
		//
		// Analyzing it only if it's a string.
		if(is_string($label)) {
			$matches = false;
			//
			// Checking if it's flagged, otherwise it stays as
			// default.
			if(preg_match('~@(?P<key>(.*))~', $label, $matches)) {
				$out = $tr->{$matches['key']};
			}
		}

		return $out;
	}
}
