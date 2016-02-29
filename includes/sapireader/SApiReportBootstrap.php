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
	 * @param type $list API results on which to work.
	 * @return string Returns a HTML piece of code.
	 */
	public function render($list) {
		//
		// Default values.
		$out = '';
		//
		// Table headers.
		$out.= "<table class=\"table table-striped\">\n";
		$out.= "\t<thead>\n";
		foreach($this->_conf->columns as $column) {
			$title = SApiReporter::TranslateLabel($column->title);
			$out.= "\t\t\t<th>{$title}</th>\n";
		}
		$out.= "\t</thead>\n";
		//
		// Table bodies.
		$out.= "\t<tbody>\n";
		foreach($list as $item) {
			//
			// Building current row.
			$out.= "\t\t<tr>\n";
			foreach($this->_conf->columns as $column) {
				$out.= "\t\t\t<td>";
				$out.= $this->buildColumn($column, $item);
				$out.= "</td>\n";
			}
			$out.= "\t\t</tr>\n";
		}
		$out.= "\t</tbody>\n";
		$out.= "</table>\n";

		return $out;
	}
	//
	// Protected methods.
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return string @TODO doc
	 */
	protected function buildButtonLinkColumn($columnConf, $item) {
		$value = '';
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}

		$out = "<button{$this->buildAttributes($columnConf, ['btn'])} onclick=\"location.href='{$value}';return false;\">";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</button>";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return type @TODO doc
	 */
	protected function buildCodeColumn($columnConf, $item) {
		$value = SApiReporter::GetPathValue($item, $columnConf->path);

		$out = "<pre{$this->buildAttributes($columnConf)}>{$value}</pre>";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return type @TODO doc
	 */
	protected function buildImageColumn($columnConf, $item) {
		$value = '';
		if(isset($columnConf->src->prefix)) {
			$value.= $columnConf->src->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->src->suffix)) {
			$value.= $columnConf->src->suffix;
		}

		$out = "<img src=\"{$value}\"{$this->buildAttributes($columnConf, ['img-responsive'])}/>";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return string @TODO doc
	 */
	protected function buildLinkColumn($columnConf, $item) {
		$value = '';
		if(isset($columnConf->link->prefix)) {
			$value.= $columnConf->link->prefix;
		}
		$value.= SApiReporter::GetPathValue($item, $columnConf->path);
		if(isset($columnConf->link->suffix)) {
			$value.= $columnConf->link->suffix;
		}

		$out = "<a href=\"{$value}\"{$this->buildAttributes($columnConf, ['btn', 'btn-link'])}>";
		$out.= $this->guessLabel($columnConf, $item, $value);
		$out.= "</a>";

		return $out;
	}
	/**
	 * @TODO doc
	 *
	 * @param type $columnConf @TODO doc
	 * @param type $item @TODO doc
	 * @return type @TODO doc
	 */
	protected function buildTextColumn($columnConf, $item) {
		$value = SApiReporter::GetPathValue($item, $columnConf->path);
		$value = htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value);

		return "<span{$this->buildAttributes($columnConf)}>{$value}</span>";
	}
}
