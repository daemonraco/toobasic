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
class SApiReportBasic extends SApiReportType {
	//
	// Public methods.
	/**
	 * This method renders resutls of an API call into a HTML table based on
	 * a Simple API Report configurations.
	 *
	 * @param type $list API results on which to work.
	 * @return string Returns a HTML piece of code.
	 */
	public function render($list) {
		//
		// Default values.
		$out = '';
		//
		// Shortcuts.
		$tr = Translate::Instance();
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
			// Building current row.
			$out.= "\t\t<tr>\n";
			foreach($this->_conf->columns as $column) {
				$path = implode('->', explode('/', $column->path));
				$value = SApiReporter::GetPathValue($item, $path);

				$out.= "\t\t\t<td>";
				switch($column->type) {
					case GC_SAPIREPORT_COLUMNTYPE_IMAGE:
						$out.= "<img src=\"{$value}\"{$this->extraAttributes($column)}/>";
						break;
					case GC_SAPIREPORT_COLUMNTYPE_LINK:
					case GC_SAPIREPORT_COLUMNTYPE_BUTTONLINK:
						$label = $tr->go_to;
						if(isset($column->extras->label)) {
							$label = $tr->{$column->extras->label};
						}
						$out.= "<a href=\"{$value}\"{$this->extraAttributes($column)}>{$label}</a>";
						break;
					case GC_SAPIREPORT_COLUMNTYPE_CODE:
						$out.='<pre>'.htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value).'</pre>';
						break;
					case GC_SAPIREPORT_COLUMNTYPE_TEXT:
					default:
						$out.=htmlentities(is_object($value) || is_array($value) ? serialize($value) : $value);
						break;
				}
				$out.= "</td>\n";
			}
			$out.= "\t\t</tr>\n";
		}
		$out.= "\t</tbody>\n";
		$out.= "</table>\n";

		return $out;
	}
}
