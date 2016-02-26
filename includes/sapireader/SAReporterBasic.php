<?php

/**
 * @file SAReporterBasic.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

//
// Class aliases
use TooBasic\Translate;

/**
 * @class SAReporterBasic
 * This class defines the logic to render a Simple API Report as a basic table.
 */
class SAReporterBasic extends SAReporterType {
	//
	// Public methods.
	/**
	 * This method renders resutls of an API call into a HTML table based on
	 * a Simple API Report configurations.
	 *
	 * @param type $results API results on which to work.
	 * @return string Returns a HTML piece of code.
	 */
	public function render($results) {
		//
		// Default values.
		$out = '';
		//
		// Shortcuts.
		$tr = Translate::Instance();
		//
		// Getting a shortcut to the list of items inside into results.
		if($this->_conf->listPath) {
			eval("\$list=\$results->{$this->_conf->listPath};");
		} else {
			$list = $results;
		}
		//
		// Table headers.
		$out.= "<table>\n";
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
				$path = implode('->', explode('/', $column->path));
				$value = $this->getPathValue($item, $path);

				$entry.= "\t\t\t<td>";
				switch($column->type) {
					case GC_SAPIREPORT_COLUMNTYPE_IMAGE:
						$entry.= "<img src=\"{$value}\"{$this->extraAttributes($column)}/>";
						break;
					case GC_SAPIREPORT_COLUMNTYPE_LINK:
					case GC_SAPIREPORT_COLUMNTYPE_BUTTONLINK:
						$label = $tr->go_to;
						if(isset($column->extras->label)) {
							$label = $tr->{$column->extras->label};
						}
						$entry.= "<a href=\"{$value}\"{$this->extraAttributes($column)}>{$label}</a>";
						break;
					case GC_SAPIREPORT_COLUMNTYPE_CODE:
						$entry.='<pre>'.htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value).'</pre>';
						break;
					case GC_SAPIREPORT_COLUMNTYPE_TEXT:
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
