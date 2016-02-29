<?php

/**
 * @file SAReporterType.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class SAReporterType
 * @abstract
 * This abstract class defines the basic logic for a Simple API Report renderer.
 */
abstract class SApiReportType {
	//
	// Protected properties.
	/**
	 * @var \stdClass Current report configuration shortcut.
	 */
	protected $_conf = false;
	//
	// Magic methods.
	/**
	 * Class constructor.
	 *
	 * @param \stdClass $conf Report configuration to work with.
	 */
	public function __construct($conf) {
		$this->_conf = $conf;
	}
	//
	// Public methods.
	/**
	 * This method renders resutls of an API call into a HTML table based on
	 * a Simple API Report configurations.
	 *
	 * @param type $list API results on which to work.
	 * @return string Returns a HTML piece of code.
	 */
	abstract public function render($list);
	//
	// Protected methods.
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $class @TODO doc
	 * @return string @TODO doc
	 */
	protected function extraCssClass($columnConf, $class = []) {
		$out = '';

		if(isset($columnConf->extras)) {
			
		}

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @return type @TODO doc
	 */
	protected function extraAttributes($columnConf) {
		$out = '';

		if(isset($columnConf->extras)) {
			$exceptions = ['class', 'label'];
			foreach(get_object_vars($columnConf->extras) as $name => $value) {
				if(in_array($name, $exceptions)) {
					continue;
				}
				if(is_object($value)) {
					$aux = '';
					foreach(get_object_vars($value) as $k => $v) {
						$aux.= "{$k}:{$v};";
					}
					$out.= " {$name}=\"{$aux}\"";
				} else {
					$out.= " {$name}=\"{$value}\"";
				}
			}
		}

		return $out;
	}
}
