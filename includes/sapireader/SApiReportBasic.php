<?php

/**
 * @file SAReporterBasic.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class SAReporterBasic
 * This class defines the logic to render a Simple API Report as a basic table.
 */
class SApiReportBasic extends SApiReportType {
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
	public function render($list, $spacer = '') {
		//
		// Default values.
		$out = '';
		//
		// Table headers.
		$out.= "{$spacer}<table id=\"{$this->_conf->name}\"{$this->buildAttributes($this->_conf)}>\n";
		$out.= "{$spacer}\t<thead>\n";
		$out.= "{$spacer}\t\t<tr>\n";
		foreach($this->_conf->columns as $column) {
			$title = SApiReporter::TranslateLabel($column->title);
			$out.= "\t\t\t<th>{$title}</th>\n";
		}
		$out.= "{$spacer}\t\t</tr>\n";
		$out.= "{$spacer}\t</thead>\n";
		//
		// Table bodies.
		$out.= "{$spacer}\t<tbody>\n";
		foreach($list as $item) {
			//
			// Building current row.
			$out.= "{$spacer}\t\t<tr>\n";
			foreach($this->_conf->columns as $column) {
				$out.= "{$spacer}\t\t\t<td>\n";
				$out.= $this->buildColumn($column, $item, "{$spacer}\t\t\t\t");
				$out.= "{$spacer}</td>\n";
			}
			$out.= "{$spacer}\t\t</tr>\n";
		}
		$out.= "{$spacer}\t</tbody>\n";
		$out.= "{$spacer}</table>\n";

		return $out;
	}
	//
	// Protected methods.
	/**
	 * This method reports certain value as a button.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildButtonLinkColumn($columnConf, $item, $spacer) {
		//
		// Default values.
		$value = '';
		//
		// Building URL.
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}
		//
		// Building HTML.
		$out = "{$spacer}<button{$this->buildAttributes($columnConf)} onclick=\"location.href='{$value}';return false;\">";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</button>\n";

		return $out;
	}
	/**
	 * This method reports certain value encased in a 'PRE' tag.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildCodeColumn($columnConf, $item, $spacer) {
		return "{$spacer}<pre{$this->buildAttributes($columnConf)}>".SApiReporter::GetPathValue($item, $columnConf->path)."</pre>\n";
	}
	/**
	 * This method reports certain value as an image using a 'IMG' tag.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildImageColumn($columnConf, $item, $spacer) {
		//
		// Default values.
		$value = '';
		//
		// Building URL.
		if(isset($columnConf->src->prefix)) {
			$value.= $columnConf->src->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->src->suffix)) {
			$value.= $columnConf->src->suffix;
		}

		return "{$spacer}<img src=\"{$value}\"{$this->buildAttributes($columnConf)}/>\n";
	}
	/**
	 * This method reports certain value as an anchor.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildLinkColumn($columnConf, $item, $spacer) {
		//
		// Default values.
		$value = '';
		//
		// Building URL.
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}
		//
		// Building HTML.
		$out = "{$spacer}<a href=\"{$value}\"{$this->buildAttributes($columnConf)}>";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</a>\n";

		return $out;
	}
	/**
	 * This method reports certain value simple escaped string encased in
	 * 'SPAN' tags.
	 *
	 * @param \stdClass $columnConf Current column configuration.
	 * @param \stdClass $item Current row information.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML code.
	 */
	protected function buildTextColumn($columnConf, $item, $spacer) {
		$value = SApiReporter::GetPathValue($item, $columnConf->path);
		//
		// Escaping tags.
		$value = htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value);

		return "{$spacer}<span{$this->buildAttributes($columnConf)}>{$value}</span>\n";
	}
}
