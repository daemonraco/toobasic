<?php

/**
 * @file FormsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Forms;

//
// Class aliases.
use TooBasic\Paths;
use TooBasic\Sanitizer;

/**
 * @class FormsManager
 * This class holds all the main logic to manage form specifications.
 */
class FormsManager extends \TooBasic\Managers\Manager {
	//
	// Public methods.
	/**
	 * This method attepmts to create a basic from specification file based on
	 * a name.
	 *
	 * @param string $name Name to use when creating the form's specification
	 * file.
	 * @param string $module Specifies a module name for cases where the
	 * specification has to be created inside a module.
	 * @return mixed[string] Returns a strucutre with information about the
	 * operation.
	 */
	public function createForm($name, $module = false) {
		//
		// Default values.
		$out = array(
			GC_AFIELD_STATUS => true,
			GC_AFIELD_ERROR => false,
			GC_AFIELD_PATH => false
		);
		//
		// Global dependencies.
		global $Directories;
		global $Paths;
		//
		// Guessing paths.
		$dir = '';
		if($module) {
			$dir = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_MODULES]}/{$module}/{$Paths[GC_PATHS_FORMS]}");
		} else {
			$dir = Sanitizer::DirPath("{$Directories[GC_DIRECTORIES_SITE]}/{$Paths[GC_PATHS_FORMS]}");
		}
		$out[GC_AFIELD_PATH] = "{$dir}/{$name}.".Paths::ExtensionJSON;
		//
		// Checking form existence.
		$auxForm = new Form($name);
		if($out[GC_AFIELD_STATUS] && $auxForm->path()) {
			$out[GC_AFIELD_ERROR] = "Form '{$name}' is already specified at '{$auxForm->path()}'";
			$out[GC_AFIELD_STATUS] = false;
		}
		unset($auxForm);
		//
		// Checking directory existence.
		if($out[GC_AFIELD_STATUS]) {
			if(!is_dir($dir)) {
				//
				// Creating directory.
				mkdir($dir, 0755, true);
				//
				// Rechecking.
				if(!is_dir($dir)) {
					$out[GC_AFIELD_ERROR] = "Unable to create directory '{$dir}'";
					$out[GC_AFIELD_STATUS] = false;
				}
			}
		}
		//
		// Checking form specification existence.
		if($out[GC_AFIELD_STATUS] && is_file($out[GC_AFIELD_PATH])) {
			$out[GC_AFIELD_ERROR] = "File '{$out[GC_AFIELD_PATH]}' already exists";
			$out[GC_AFIELD_STATUS] = false;
		}
		//
		// Generating basic structure and saving.
		if($out[GC_AFIELD_STATUS]) {
			$contents = new \stdClass();
			$contents->form = new \stdClass();
			$contents->form->name = $name;
			$contents->form->fields = new \stdClass();

			if(!file_put_contents($out[GC_AFIELD_PATH], json_encode($contents, JSON_PRETTY_PRINT))) {
				$out[GC_AFIELD_ERROR] = "Unable to save initial structure into '{$out[GC_AFIELD_PATH]}'";
				$out[GC_AFIELD_STATUS] = false;
			}
		}
		//
		// Returning results.
		return $out;
	}
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
	/**
	 * This method attepmts to delete a from specification file based on its
	 * name.
	 *
	 * @param string $name Specification file name.
	 * @return mixed[string] Returns a strucutre with information about the
	 * operation.
	 */
	public function removeForm($name) {
		//
		// Default values.
		$out = array(
			GC_AFIELD_STATUS => true,
			GC_AFIELD_ERROR => false,
			GC_AFIELD_PATH => false
		);
		//
		// Checking form existence.
		$form = new Form($name);
		if($out[GC_AFIELD_STATUS] && !$form->path()) {
			$out[GC_AFIELD_ERROR] = "Unable to find form '{$name}' specification";
			$out[GC_AFIELD_STATUS] = false;
		} else {
			$out[GC_AFIELD_PATH] = $form->path();
		}
		//
		// Removing specification.
		if($out[GC_AFIELD_STATUS]) {
			@unlink($out[GC_AFIELD_PATH]);
			//
			// Checking.
			if(is_file($out[GC_AFIELD_PATH])) {
				$out[GC_AFIELD_ERROR] = "Unable to remove specification file at '{$out[GC_AFIELD_PATH]}'";
				$out[GC_AFIELD_STATUS] = false;
			}
		}
		//
		// Returning results.
		return $out;
	}
}
