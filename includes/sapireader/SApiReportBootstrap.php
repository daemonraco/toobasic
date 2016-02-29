<?php

/**
 * @file SAReporterBootstrap.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class SAReporterBootstrap
 * This class defines the logic to render a Simple API Report as a table using
 * Twitter Bootstrap styles.
 */
class SApiReportBootstrap extends SApiReportType {
	//
	// Public methods.
	/**
	 * This method renders resutls of an API call into a HTML table based on
	 * a Simple API Report configurations using Twitter Bootstrap styles.
	 *
	 * @param mixed $list API results on which to work.
	 * @param string $spacer String to prefix on each line.
	 * @return string Returns a HTML piece of code.
	 */
	public function render($list, $spacer = '') {
		//
		// Default values.
		$out = '';
		//
		// Table headers.
		$out.= "{$spacer}<table id=\"{$this->_conf->name}\"{$this->buildAttributes($this->_conf, ['table', 'table-striped'])}>\n";
		$out.= "{$spacer}\t<thead>\n";
		$out.= "{$spacer}\t\t<tr>\n";
		foreach($this->_conf->columns as $column) {
			$title = SApiReporter::TranslateLabel($column->title);
			$out.= "{$spacer}\t\t\t<th>{$title}</th>\n";
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
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param string $spacer String to prefix on each line.
	 * @return string @TODO doc
	 */
	protected function buildButtonLinkColumn($columnConf, $item, $spacer) {
		$value = '';
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}

		$out = "{$spacer}<button{$this->buildAttributes($columnConf, ['btn'])} onclick=\"location.href='{$value}';return false;\">";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</button>\n";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param string $spacer String to prefix on each line.
	 * @return type @TODO doc
	 */
	protected function buildCodeColumn($columnConf, $item, $spacer) {
		$value = SApiReporter::GetPathValue($item, $columnConf->path);

		$out = "{$spacer}<pre{$this->buildAttributes($columnConf)}>{$value}</pre>\n";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param string $spacer String to prefix on each line.
	 * @return type @TODO doc
	 */
	protected function buildImageColumn($columnConf, $item, $spacer) {
		$value = '';
		if(isset($columnConf->src->prefix)) {
			$value.= $columnConf->src->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->src->suffix)) {
			$value.= $columnConf->src->suffix;
		}

		$out = "{$spacer}<img src=\"{$value}\"{$this->buildAttributes($columnConf, ['img-responsive'])}/>\n";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param string $spacer String to prefix on each line.
	 * @return string @TODO doc
	 */
	protected function buildLinkColumn($columnConf, $item, $spacer) {
		$value = '';
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}

		$out = "{$spacer}<a href=\"{$value}\"{$this->buildAttributes($columnConf, ['btn', 'btn-link'])}>";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</a>\n";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @param string $spacer String to prefix on each line.
	 * @return type @TODO doc
	 */
	protected function buildTextColumn($columnConf, $item, $spacer) {
		$value = SApiReporter::GetPathValue($item, $columnConf->path);
		$value = htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value);

		return "{$spacer}<span{$this->buildAttributes($columnConf)}>{$value}</span>\n";
	}
}
