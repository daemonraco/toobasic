<?php

/**
 * @file ManifestsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use \TooBasic\Manifest;

/**
 * @class ManifestsManager
 * This singleton class holds the logic to manages module manifests.
 */
class ManifestsManager extends Manager {
	//
	// Protected proterties.
	/**
	 * @var mixed[] List of found errors while checking.
	 */
	protected $_errors = array();
	/**
	 * @var boolean This flag is TRUE when the site is flagged as installed.
	 */
	protected $_installed = false;
	/**
	 * @var \TooBasic\Manifest[string] List of known manifests associated with
	 * their module names.
	 */
	protected $_manifests = false;
	//
	// Public methods.
	/**
	 * This method checks every manifest status.
	 *
	 * @param boolean $forced When TRUE, checks even if site is flagged as
	 * installed.
	 * @return boolean Returns TRUE when there were no errors.
	 */
	public function check($forced = false) {
		//
		// Checks are run only when the site is not flagged as installed.
		if($forced || !$this->_installed) {
			//
			// Checking each module.
			foreach($this->manifests() as $module => $manifest) {
				//
				// Checking manifest.
				if(!$manifest->check()) {
					//
					// Appending manifest errors.
					foreach($manifest->errors() as $error) {
						$this->setError($error[GC_AFIELD_CODE], $error[GC_AFIELD_MESSAGE], $module);
					}
				}
			}
		}

		return !$this->hasErrors();
	}
	/**
	 * This method provides access a list of errors found while loading or
	 * checking.
	 *
	 * @return mixed[] Returns a list of errors.
	 */
	public function errors() {
		return $this->_errors;
	}
	/**
	 * This method allows to know if there was an error while loading or
	 * checking.
	 *
	 * @return boolean Returns TRUE if at least one error was found.
	 */
	public function hasErrors() {
		return \boolval($this->_errors);
	}
	/**
	 * This method provides access to all loaded manifests.
	 *
	 * @return \TooBasic\Manifest[string] Returns a list of manifests.
	 */
	public function manifests() {
		//
		// Enforcing loading.
		$this->loadManifests();
		return $this->_manifests;
	}
	//
	// Protected methods.
	/**
	 * Manager's initilization.
	 */
	protected function init() {
		parent::init();
		//
		// Global dependencies.
		global $Defaults;

		$this->_installed = $Defaults[GC_DEFAULTS_INSTALLED];

		if(isset($this->params->debugmanifests)) {
			\TooBasic\debugThingInPage(function() {
				foreach($this->manifests() as $manifest) {
					$info = $manifest->information();

					echo '<div class="panel panel-default">';
					echo "<div class=\"panel-heading\">{$info->name} (v{$info->version})</div>";
					echo '<div class="panel-body">';
					echo '<table class="table">';

					if($info->description) {
						echo '<tr>';
						echo '<td><strong>Description</strong>:</td>';
						echo "<td>{$info->description}</td>";
						echo '</tr>';
					}
					if($info->author->name) {
						echo '<tr>';
						echo '<td><strong>Author</strong>:</td>';
						if($info->author->page) {
							echo "<td><a href=\"{$info->author->page}\" target=\"_blank\">{$info->author->name}</td>";
						} else {
							echo "<td>{$info->author->name}</td>";
						}
						echo '</tr>';
					}
					if($info->copyright) {
						echo '<tr>';
						echo '<td><strong>Copyright</strong>:</td>';
						echo "<td>&copy; {$info->copyright} {$info->author->name}</td>";
						echo '</tr>';
					}
					if($info->license) {
						echo '<tr>';
						echo '<td><strong>License</strong>:</td>';
						echo "<td>{$info->license}</td>";
						echo '</tr>';
					}
					if($info->url) {
						echo '<tr>';
						echo '<td><strong>Page</strong>:</td>';
						echo "<td><a href=\"{$info->url}\" target=\"_blank\">{$info->url}</td>";
						echo '</tr>';
					}
					if($info->url_doc && $info->url_doc != $info->url) {
						echo '<tr>';
						echo '<td><strong>Documentation</strong>:</td>';
						echo "<td><a href=\"{$info->url_doc}\" target=\"_blank\">{$info->url_doc}</td>";
						echo '</tr>';
					}

					echo '<tr>';
					echo '<td><strong>Requirements</strong>:</td>';
					echo '<td><ul>';
					echo "<li><strong>PHP</strong>: {$info->required_versions->php}</li>";
					echo "<li><strong>TooBasic</strong>: {$info->required_versions->toobasic}</li>";
					echo '</ul></td>';
					echo '</tr>';

					echo '</table>';
					echo '</div>';
					echo '</div>';
				}

				if(!$this->check(true)) {
					echo '<div class="panel panel-default">';
					echo "<div class=\"panel-heading\">Check Errors</div>";
					echo '<div class="panel-body">';
					echo '<table class="table">';
					echo '<tr>';
					echo '<th>Code</th>';
					echo '<th>Message</th>';
					echo '<th>Module</th>';
					echo '</tr>';
					foreach($this->errors() as $error) {
						echo '<tr>';
						echo "<td>{$error[GC_AFIELD_CODE]}</td>";
						echo "<td>{$error[GC_AFIELD_MESSAGE]}</td>";
						echo "<td>{$error[GC_AFIELD_MODULE_NAME]}</td>";
						echo '</tr>';
					}
					echo '</table>';
					echo '</div>';
					echo '</div>';
				}
			}, 'Manifests');
		}
	}
	/**
	 * This method loads evrey module manifest.
	 */
	protected function loadManifests() {
		//
		// Avoiding multiple loads.
		if($this->_manifests === false) {
			$this->_manifests = array();
			//
			// Loading a representation for each module.
			foreach($this->paths->modules() as $module) {
				$this->_manifests[$module] = new Manifest($module);
			}
		}
	}
	/**
	 * This method adds an error to the internal list.
	 *
	 * @param int $code Error code.
	 * @param string $message Message explaining the error.
	 * @param string $module Name of the module, if any, where the error was
	 * found.
	 */
	protected function setError($code, $message, $module = false) {
		$this->_errors[] = array(
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_MODULE_NAME => $module
		);
	}
}
