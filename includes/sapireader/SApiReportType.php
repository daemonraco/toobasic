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
	protected function buildAttributes($columnConf, $class = []) {
		$out = '';

		if(isset($columnConf->attrs)) {
			$exceptions = ['class', 'id'];
			foreach(get_object_vars($columnConf->attrs) as $name => $value) {
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

			if(!is_array($class)) {
				$class = [];
			}
			if(isset($columnConf->attrs->class)) {
				if(!is_array($columnConf->attrs->class)) {
					$columnConf->attrs->class = explode(' ', $columnConf->attrs->class);
				}

				$class = array_merge($class, $columnConf->attrs->class);
			}
			if($class) {
				$out.= ' class="'.implode(' ', $class).'"';
			}
		}

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return type @TODO doc
	 */
	protected function buildColumn($columnConf, $item) {
		$method = 'build'.str_replace(' ', '', ucwords(str_replace('-', ' ', $columnConf->type))).'Column';
		if(!method_exists($this, $method)) {
			$method = 'buildTextColumn';
		}

		return $this->{$method}($columnConf, $item);
	}
	/**
	 * @TODO doc
	 */
	abstract protected function buildButtonLinkColumn($columnConf, $item);
	/**
	 * @TODO doc
	 */
	abstract protected function buildCodeColumn($columnConf, $item);
	/**
	 * @TODO doc
	 */
	abstract protected function buildImageColumn($columnConf, $item);
	/**
	 * @TODO doc
	 */
	abstract protected function buildLinkColumn($columnConf, $item);
	/**
	 * @TODO doc
	 */
	abstract protected function buildTextColumn($columnConf, $item);
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param type $default @TODO doc
	 * @return type @TODO doc
	 */
	protected function guessLabel($columnConf, $item, $default) {
		$out = '';

		if($columnConf->label_field) {
			$out = SApiReporter::GetPathValue($item, $columnConf->label_field);
		}

		if(!$out && $columnConf->label) {
			$out = SApiReporter::TranslateLabel($columnConf->label);
		}

		if(!$out) {
			$out = $default;
		}

		return $out;
	}
}
