<?php

namespace TooBasic\Forms;

class BasicType extends FormType {
	public function buildFor($item, $mode, $flags) {
		//
		// Default values.
		$out = '';
		//
		// Shortcuts.
		$tr = $this->translate;
		//
		// Expanding build flags.
		$this->expandBuildFlags($flags);
		//
		// Is read only?
		$readOnly = $this->isReadOnly($mode);
		//
		// Form tag.
		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}<form";
		if($this->_config->form->name) {
			$out.= " id=\"{$this->_config->form->name}\"";
		}
		$out.= " action=\"{$flags[GC_FORMS_BUILDFLAG_ACTION]}\" method=\"{$flags[GC_FORMS_BUILDFLAG_METHOD]}\"";
		if($flags[GC_FORMS_BUILDFLAG_ONSUBMIT]) {
			$out.= " onsubmit=\"{$flags[GC_FORMS_BUILDFLAG_ONSUBMIT]}\"";
		}
		$out.= $this->attrsToString($this->_config->form->attrs);
		$out.= ">\n";
		//
		// Fields.
		$fields = array();
		foreach($this->_config->form->fields as $name => $config) {
			if($readOnly) {
				$config->attrs->readonly = 'readonly';
			}

			if($config->type == 'hidden') {
				$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<input id=\"{$this->_config->form->name}_{$name}\"";
				if(!$readOnly) {
					$out.= " name=\"{$name}\"";
				}
				$out.= " type=\"hidden\" value=\"".(isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : '')."\"/>\n";
			} elseif($config->type == 'input' || $config->type == 'password' || ( $readOnly && $config->type == 'enum')) {
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<input id=\"{$this->_config->form->name}_{$name}\"";
				if(!$readOnly) {
					$aux.= " name=\"{$name}\"";
				}
				$aux.= " type=\"".($config->type == 'password' ? 'password' : 'text').'"';
				foreach(get_object_vars($config->attrs) as $k => $v) {
					if($v === true) {
						$aux.= " {$k}";
					} else {
						$aux.= " {$k}=\"{$v}\"";
					}
				}
				if($mode != GC_FORMS_BUILDMODE_CREATE) {
					if($config->type != 'enum') {
						$aux.= " value=\"".(isset($item[$name]) ? $item[$name] : $config->value)."\"/>\n";
					} else {
						$value = isset($item[$name]) ? $item[$name] : $config->value;
						$trValue = $tr->{"select_option_{$value}"};
						$aux.= " value=\"{$trValue}\"/>\n";
					}
				} else {
					$aux.= "/>\n";
				}

				$fields[] = $aux;
			} elseif($config->type == 'enum') {
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<select id=\"{$this->_config->form->name}_{$name}\"";
				if(!$readOnly) {
					$aux.= " name=\"{$name}\"";
				}
				$aux.= $this->attrsToString($config->attrs);
				$aux.= ">\n";

				$selectedValue = isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : $config->value;
				foreach($config->values as $value) {
					$trValue = $tr->{"select_option_{$value}"};
					$selected = $selectedValue == $value ? ' selected="selected"' : '';
					$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<option value=\"{$value}\"{$selected}>{$trValue}</option>\n";
				}

				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t</select>\n";

				$fields[] = $aux;
			} elseif($config->type == 'text') {
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<textarea id=\"{$this->_config->form->name}_{$name}\"";
				if(!$readOnly) {
					$aux.= " name=\"{$name}\"";
				}
				$aux.= $this->attrsToString($config->attrs);
				$aux.= '>'.(isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : $config->value)."</textarea>\n";

				$fields[] = $aux;
			} else {
				throw new FormsException("Unknown field type '{$config->type}' at path '///form/fields/{$name}'.");
			}
		}
		$out.= "\n".implode("\n\n\n", $fields);

//		foreach()

		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}</form>\n";

		return preg_replace("%\n([\n]+)%", "\n\n", $out);
	}
}
