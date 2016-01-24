<?php

/**
 * @file BasicType.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class BasicType
 * @todo doc
 */
class BasicType extends FormType {
	/**
	 * This method build a HTML form code based on its configuration.
	 *
	 * @param mixed[string] $item Information to fill fields (except for mode
	 * 'create').
	 * @param string $mode Mode in which it must be built.
	 * @param mixed[string] $flags List of extra parameters used to build.
	 * @return string Returns a HTML piece of code.
	 * @throws \TooBasic\Forms\FormsException
	 */
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
		$out.= " action=\"{$this->action($mode)}\" method=\"{$this->method($mode)}\"";
		$out.= $this->attrsToString($this->attrs($mode));
		$out.= ">\n";
		//
		// Fields.
		$fields = array();
		foreach($this->_config->form->fields as $name => $config) {
			//
			// Ignoring fields excluded from current mode.
			if(in_array($mode, $config->excludedModes)) {
				continue;
			}
			//
			// Forcing the readonly attribute.
			if($readOnly) {
				$config->attrs->readonly = 'readonly';
			}
			//
			// Checking and building based un current field type.
			if($config->type == 'hidden') {
				//
				// Building a hidden input.
				//
				$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<input id=\"{$this->_config->form->name}_{$name}\" name=\"{$name}\"";
				$out.= " type=\"hidden\" value=\"".(isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : '')."\"/>\n";
			} elseif($config->type == 'input' || $config->type == 'password' || ( $readOnly && $config->type == 'enum')) {
				//
				// Building a text input or a select in read-only
				// mode.
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<input id=\"{$this->_config->form->name}_{$name}\" name=\"{$name}\"";
				$aux.= " type=\"".($config->type == 'password' ? 'password' : 'text').'"';
				$aux.= $this->attrsToString($config->attrs);
				//
				// Checking if it should add current value or not.
				if($mode != GC_FORMS_BUILDMODE_CREATE) {
					//
					// Checking the proper way to get current
					// values.
					if($config->type != 'enum') {
						$aux.= " value=\"".(isset($item[$name]) ? $item[$name] : $config->value)."\"";
					} else {
						$value = isset($item[$name]) ? $item[$name] : $config->value;
						$trValue = $tr->{"select_option_{$value}"};
						$aux.= " value=\"{$trValue}\"";
					}
				}
				$aux.= "/>\n";

				$fields[] = $aux;
			} elseif($config->type == 'enum') {
				//
				// Building a select
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<select id=\"{$this->_config->form->name}_{$name}\" name=\"{$name}\"";
				$aux.= $this->attrsToString($config->attrs);
				$aux.= ">\n";
				//
				// Currently selected value.
				$selectedValue = isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : $config->value;
				//
				// Empty value.
				if(isset($config->emptyOption)) {
					$trValue = $tr->{$config->emptyOption->label};
					$selected = $selectedValue == $config->emptyOption->value ? ' selected="selected"' : '';
					$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t<option value=\"{$config->emptyOption->value}\"{$selected}>{$trValue}</option>\n";
				}
				//
				// All possible values.
				foreach($config->values as $value) {
					$trValue = $tr->{"select_option_{$value}"};
					$selected = $selectedValue == $value ? ' selected="selected"' : '';
					$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<option value=\"{$value}\"{$selected}>{$trValue}</option>\n";
				}

				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t</select>\n";

				$fields[] = $aux;
			} elseif($config->type == 'text') {
				//
				// Building a textarea.
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<label for=\"{$this->_config->form->name}_{$name}\">".$tr->{$config->label}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<textarea id=\"{$this->_config->form->name}_{$name}\" name=\"{$name}\"";
				$aux.= $this->attrsToString($config->attrs);
				$aux.= '>'.(isset($item[$name]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$name] : $config->value)."</textarea>\n";

				$fields[] = $aux;
			} else {
				throw new FormsException("Unknown field type '{$config->type}' at path '///form/fields/{$name}'.");
			}
		}
		//
		// Appending all generated fields.
		$out.= "\n".implode("\n\n\n", $fields);
		//
		// Generating buttons.
		$buttons = array();
		foreach($this->buttonsFor($mode) as $name => $config) {
			$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<button type=\"{$config->type}\" id=\"{$this->_config->form->name}_{$name}\"";
			$aux.= $this->attrsToString($config->attrs);
			$aux.= ">";
			$aux.= $tr->{$config->label}.'</button>';

			$buttons[] = $aux;
		}
		if($buttons) {
			$out.= "\n".implode("\n", $buttons)."\n";
		}

		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}</form>\n";

		return preg_replace("%\n([\n]+)%", "\n\n", $out);
	}
}
