<?php

/**
 * @file SAReporterBootstrap.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases
use TooBasic\Translate;

/**
 * @class SAReporterBootstrap
 * This class defines the logic to render a Simple API Report as a table using
 * Twitter Bootstrap styles.
 */
class SAReporterBootstrap extends SAReporterType {
	//
	// Public methods.
	/**
	 * This method renders resutls of an API call into a HTML table based on
	 * a Simple API Report configurations using Twitter Bootstrap styles.
	 *
	 * @param type $results API results on which to work.
	 * @return string Returns a HTML piece of code.
	 */
	public function render($results) {
		//
		// Defult values.
		$out = '';
		//
		// Shortcuts.
		$tr = Translate::Instance();
		//
		// Getting a shortcut to the list of items inside into results.
		if($this->_conf->listPath) {
			$list = $this->getPathValue($results, $this->_conf->listPath);
		} else {
			$list = $results;
		}
		//
		// Table headers.
		$out.= "<table class=\"table table-striped\">\n";
		$out.= "\t<thead>\n";
		foreach($this->_conf->columns as $column) {
			$title = $tr->{$column->title};
			$out.= "\t\t\t<th>{$title}</th>\n";
		}
		$out.= "\t</thead>\n";
		//
		// Table bodies.
		$out.= "\t<tbody>\n";
		foreach($list as $item) {
			//
			// Checking this this row is excluded or not.
			$entryOk = !$this->isRowExcluded($item);
			if(!$entryOk) {
				continue;
			}
			//
			// Building current row.
			$entry = "\t\t<tr>\n";
			foreach($this->_conf->columns as $column) {
				$value = $this->getPathValue($item, $column->path);

				$entry.= "\t\t\t<td>";
				switch($column->type) {
					case GC_SAREPORT_COLUMNTYPE_IMAGE:
						$entry.= "<img class=\"img-responsive\" src=\"{$value}\"{$this->extraAttributes($column)}/>";
						break;
					case GC_SAREPORT_COLUMNTYPE_LINK:
						$label = $tr->go_to;
						if(isset($column->extras->label)) {
							$label = $tr->{$column->extras->label};
						}
						$entry.= "<a class=\"btn\" href=\"{$value}\"{$this->extraAttributes($column)}>{$label}</a>";
						break;
					case GC_SAREPORT_COLUMNTYPE_BUTTONLINK:
						$label = $tr->go_to;
						if(isset($column->extras->label)) {
							$label = $tr->{$column->extras->label};
						}
						$entry.= "<a class=\"btn btn-link\" href=\"{$value}\"{$this->extraAttributes($column)}>{$label}</a>";
						break;
					case GC_SAREPORT_COLUMNTYPE_CODE:
						$entry.='<pre>'.htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value).'</pre>';
						break;
					case GC_SAREPORT_COLUMNTYPE_TEXT:
					default:
						$entry.=htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value);
						break;
				}
				$entry.= "</td>\n";
			}
			$entry.= "\t\t</tr>\n";

			if($entryOk) {
				$out.= $entry;
			}
		}
		$out.= "\t</tbody>\n";
		$out.= "</table>\n";

		return $out;
	}
}
