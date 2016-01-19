<?php

namespace TooBasic\Forms;

class FormsManager extends \TooBasic\Managers\Manager {
	//
	// Public methods.
	public function formFor($formName, $item = false, $mode = false, $flags = array()) {
		//
		// Default values
		$out = '';

		try {
			$form = FormsFactory::Instance()->{$formName};
			if(!$form || !$form->status()) {
				$out = "Unable to obtain form '{$formName}'";
				$out.= $form ? ' (status: '.($form->status() ? 'Ok' : 'Failed').').' : '.';
			} else {
				$out = $form->buildFor($item, $mode, $flags);
			}
		} catch(FormsException $e) {
			$out = "FormsException: {$e->getMessage()}";
		}

		return $out;
	}
}
