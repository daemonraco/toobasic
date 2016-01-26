<?php

/**
 * @file TableType.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

/**
 * @class TableType
 * @todo doc
 */
class TableType extends FormType {
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
		$readOnly = $this->_form->isReadOnly($mode);
		//
		// Form tag.
		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}<form";
		if($this->_form->id()) {
			$out.= " id=\"{$this->_form->id()}\"";
		}
		$out.= " action=\"{$this->_form->action($mode)}\" method=\"{$this->_form->method($mode)}\"";
		$out.= $this->attrsToString($this->_form->attributes($mode), true);
		$out.= ">\n";
		//
		// Fields.
		$fields = array();
		foreach($this->_form->fields() as $fieldName) {
			$fieldType = $this->_form->fieldType($fieldName);
			$fieldAttrs = $this->_form->fieldAttributes($fieldName);
			$fieldLabel = $this->_form->fieldLabel($fieldName);
			$fieldValue = $this->_form->fieldValue($fieldName);
			$fieldEmptyOption = $this->_form->fieldEmptyOption($fieldName);
			//
			// Ignoring fields excluded from current mode.
			if($this->_form->isFieldExcluded($fieldName, $mode)) {
				continue;
			}
			//
			// Forcing the readonly attribute.
			if($readOnly) {
				$fieldAttrs->readonly = 'readonly';
			}
			//
			// Checking and building based un current field type.
			if($fieldType == 'hidden') {
				//
				// Building a hidden input.
				//
				$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<input id=\"{$this->_form->fieldId($fieldName)}\" name=\"{$fieldName}\"";
				$out.= " type=\"hidden\" value=\"".(isset($item[$fieldName]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$fieldName] : '')."\"/>\n";
			} elseif($fieldType == 'input' || $fieldType == 'password' || ( $readOnly && $fieldType == 'enum')) {
				//
				// Building a text input or a select in read-only
				// mode.
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<tr>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t<td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<label for=\"{$this->_form->fieldId($fieldName)}\">".$tr->{$fieldLabel}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td><td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<input id=\"{$this->_form->fieldId($fieldName)}\" name=\"{$fieldName}\"";
				$aux.= " type=\"".($fieldType == 'password' ? 'password' : 'text').'"';
				$aux.= $this->attrsToString($fieldAttrs);
				//
				// Checking if it should add current value or not.
				if($mode != GC_FORMS_BUILDMODE_CREATE) {
					//
					// Checking the proper way to get current
					// values.
					if($fieldType != 'enum') {
						$aux.= " value=\"".(isset($item[$fieldName]) ? $item[$fieldName] : $fieldValue)."\"";
					} else {
						$value = isset($item[$fieldName]) ? $item[$fieldName] : $fieldValue;
						$trValue = $tr->{"select_option_{$value}"};
						$aux.= " value=\"{$trValue}\"";
					}
				}
				$aux.= "/>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t</tr>";

				$fields[] = $aux;
			} elseif($fieldType == 'enum') {
				//
				// Building a select
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<tr>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t<td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<label for=\"{$this->_form->fieldId($fieldName)}\">".$tr->{$fieldLabel}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td><td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<select id=\"{$this->_form->fieldId($fieldName)}\" name=\"{$fieldName}\"";
				$aux.= $this->attrsToString($fieldAttrs);
				$aux.= ">\n";
				//
				// Currently selected value.
				$selectedValue = isset($item[$fieldName]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$fieldName] : $fieldValue;
				//
				// Empty value.
				if($fieldEmptyOption) {
					$trValue = $tr->{$fieldEmptyOption->label};
					$selected = $selectedValue == $fieldEmptyOption->value ? ' selected="selected"' : '';
					$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t\t<option value=\"{$fieldEmptyOption->value}\"{$selected}>{$trValue}</option>\n";
				}
				//
				// All possible values.
				foreach($this->_form->fieldValues($fieldName) as $value) {
					$trValue = $tr->{"select_option_{$value}"};
					$selected = $selectedValue == $value ? ' selected="selected"' : '';
					$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t\t<option value=\"{$value}\"{$selected}>{$trValue}</option>\n";
				}

				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t</select>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t</tr>";

				$fields[] = $aux;
			} elseif($fieldType == 'text') {
				//
				// Building a textarea.
				//
				$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<tr>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t<td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<label for=\"{$this->_form->fieldId($fieldName)}\">".$tr->{$fieldLabel}."</label>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td><td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<textarea id=\"{$this->_form->fieldId($fieldName)}\" name=\"{$fieldName}\"";
				$aux.= $this->attrsToString($fieldAttrs);
				$aux.= '>'.(isset($item[$fieldName]) && $mode != GC_FORMS_BUILDMODE_CREATE ? $item[$fieldName] : $fieldValue)."</textarea>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td>\n";
				$aux.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t</tr>";

				$fields[] = $aux;
			} else {
				throw new FormsException("Unknown field type '{$fieldType}' at path '///form/fields/{$fieldName}'.");
			}
		}
		//
		// Appending all generated fields.
		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t<table>\n";
		$out.= implode("\n", $fields)."\n";
		//
		// Generating buttons.
		$buttons = array();
		foreach($this->_form->buttonsFor($mode) as $buttonName) {
			$buttonType = $this->_form->buttonType($buttonName, $mode);
			$buttonId = $this->_form->buttonId($buttonName);
			$buttonAttrs = $this->_form->buttonAttributes($buttonName, $mode);
			$buttonLabel = $this->_form->buttonLabel($buttonName, $mode);

			$aux = "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t\t<button type=\"{$buttonType}\" id=\"{$buttonId}\"";
			$aux.= $this->attrsToString($buttonAttrs);
			$aux.= ">";
			$aux.= $tr->{$buttonLabel}.'</button>';

			$buttons[] = $aux;
		}
		if($buttons) {
			$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t<tr>\n";
			$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t<td colspan=\"2\">\n";
			$out.= implode("\n", $buttons)."\n";
			$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t\t</td>\n";
			$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t\t</tr>\n";
		}

		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}\t</table>\n";
		$out.= "{$flags[GC_FORMS_BUILDFLAG_SPACER]}</form>\n";

		return preg_replace("%\n([\n]+)%", "\n\n", $out);
	}
}
