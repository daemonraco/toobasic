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
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML piece of code.
	 */
	abstract public function render($list, $spacer = '');
	//
	// Protected methods.
	/**
	 * This method builds a list of attributes based configurations and
	 * returns it as a piece of HTML that can be inserted inside a HTML tag.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param string[] $class List of classes to be prepend to those already
	 * configured.
	 * @return string Returns a HTML code.
	 */
	protected function buildAttributes($columnConf, $class = []) {
		//
		// Default values.
		$out = '';
		//
		// Checking if there's a configuration for attributes.
		if(isset($columnConf->attrs)) {
			//
			// Attributs that cannot be built in a simple way.
			$exceptions = ['class', 'id'];
			//
			// Building the HTML code based on configurations.
			foreach(get_object_vars($columnConf->attrs) as $name => $value) {
				//
				// Avoiding exceptions.
				if(in_array($name, $exceptions)) {
					continue;
				}
				//
				// Checking if it's a complex specification.
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
			//
			// Checking parameter '$class' validity.
			if(!is_array($class)) {
				$class = [];
			}
			//
			// Merging classes list.
			if(isset($columnConf->attrs->class)) {
				if(!is_array($columnConf->attrs->class)) {
					$columnConf->attrs->class = explode(' ', $columnConf->attrs->class);
				}

				$class = array_merge($class, $columnConf->attrs->class);
			}
			//
			// Appending 'class' attribute.
			if($class) {
				$out.= ' class="'.implode(' ', $class).'"';
			}
		}

		return $out;
	}
	/**
	 * This proxy method forwards the call to build column values based on
	 * their types and current values.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildColumn(\stdClass $columnConf, \stdClass $item, $spacer) {
		//
		// Guessing the proper method name.
		$method = 'build'.str_replace(' ', '', ucwords(str_replace('-', ' ', $columnConf->type))).'Column';
		//
		// Checking mehtod existence.
		if(!method_exists($this, $method)) {
			$method = 'buildTextColumn';
		}
		//
		// Forwarding call.
		return $this->{$method}($columnConf, $item, $spacer);
	}
	/**
	 * This method reports certain value as a button.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	abstract protected function buildButtonLinkColumn(\stdClass $columnConf, \stdClass $item, $spacer);
	/**
	 * This method reports certain value encased in a 'PRE' tag.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	abstract protected function buildCodeColumn(\stdClass $columnConf, \stdClass $item, $spacer);
	/**
	 * This method reports certain value as an image using a 'IMG' tag.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	abstract protected function buildImageColumn(\stdClass $columnConf, \stdClass $item, $spacer);
	/**
	 * This method reports certain value as an anchor.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	abstract protected function buildLinkColumn(\stdClass $columnConf, \stdClass $item, $spacer);
	/**
	 * This method reports certain value simple escaped string encased in
	 * 'SPAN' tags.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	abstract protected function buildTextColumn(\stdClass $columnConf, \stdClass $item, $spacer);
	/**
	 * This method tries to guess the proper label for a link based on its
	 * configuration.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $default Default value to use in case failure.
	 * @return string Returns a printable label.
	 */
	protected function guessLabel(\stdClass $columnConf, \stdClass $item, $default) {
		//
		// Default values.
		$out = '';
		//
		// Checking labels based on other field value.
		if($columnConf->label_field) {
			$out = SApiReporter::GetPathValue($item, $columnConf->label_field);
		}
		//
		// Checking basic lable definition.
		if(!$out && $columnConf->label) {
			$out = SApiReporter::TranslateLabel($columnConf->label);
		}
		//
		// If no valid label was assigned, the given default is used.
		if(!$out) {
			$out = $default;
		}

		return $out;
	}
}
