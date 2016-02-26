<?php

namespace TooBasic;

use TooBasic\Translate;

class SAReporterBasic extends SAReporterType {
	public function render($results) {
		$out = '';

		$tr = Translate::Instance();

		if($this->_conf->listPath) {
			eval("\$list=\$results->{$this->_conf->listPath};");
		} else {
			$list = $results;
		}

		$out.= "<table>\n";
		$out.= "\t<thead>\n";
		foreach($this->_conf->columns as $column) {
			$title = $tr->{$column->title};
			$out.= "\t\t\t<th>{$title}</th>\n";
		}
		$out.= "\t</thead>\n";

		$out.= "\t<tbody>\n";
		foreach($list as $item) {
			$entryOk = !$this->isRowExcluded($item);
			if(!$entryOk) {
				continue;
			}

			$entry = "\t\t<tr>\n";
			foreach($this->_conf->columns as $column) {
				$path = implode('->', explode('/', $column->path));
				$value = $this->getPathValue($item, $path);

				$entry.= "\t\t\t<td>";
				switch($column->type) {
					case GC_SAREPORT_COLUMNTYPE_IMAGE:
						$entry.= "<img src=\"{$value}\"{$this->extraAttributes($column)}/>";
						break;
					case GC_SAREPORT_COLUMNTYPE_LINK:
					case GC_SAREPORT_COLUMNTYPE_BUTTONLINK:
						$label = $tr->go_to;
						if(isset($column->extras->label)) {
							$label = $tr->{$column->extras->label};
						}
						$entry.= "<a href=\"{$value}\"{$this->extraAttributes($column)}>{$label}</a>";
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
