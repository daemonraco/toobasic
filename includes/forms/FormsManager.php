<?php

/**
 * @file FormsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

class FormsManager extends \TooBasic\Managers\Manager {
	//
	// Public methods.
	/**
	 * This method triggers the loading and HTML generation of certain form
	 * for a specific mode and item (if any).
	 *
	 * @param string $formName Name of the form to load.
	 * @param mixed[string] $item Information to fill fields (except for mode
	 * 'create').
	 * @param string $mode Mode in which it must be built.
	 * @param mixed[string] $flags List of extra parameters used to build.
	 * @return string Returns a HTML piece of code.
	 */
	public function formFor($formName, $item = false, $mode = false, $flags = array()) {
		//
		// Default values
		$out = '';
		//
		// Trying to load and build the for.
		try {
			//
			// Loading the requested form.
			$form = FormsFactory::Instance()->{$formName};
			//
			// Checking form an its status.
			if(!$form || !$form->status()) {
				$out = "Unable to obtain form '{$formName}'";
				$out.= $form ? ' (status: '.($form->status() ? 'Ok' : 'Failed').').' : '.';
			} else {
				//
				// Building a HTML to be shown.
				$out = $form->buildFor($item, $mode, $flags);
			}
		} catch(FormsException $e) {
			$out = "FormsException: {$e->getMessage()}";
		}

		return $out;
	}
}
