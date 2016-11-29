<?php

/**
 * @file ManifestsManager.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic\Managers;

//
// Class aliases.
use TooBasic\Manifest;
use TooBasic\Paths;

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
	protected $_errors = [];
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
	 * This method provides access to a certain module manifest based on it's
	 * universal code.
	 *
	 * @param string $uCode Universal code to look for.
	 * @return \TooBasic\Manifest[string] Returns a manifest object.
	 */
	public function manifestByUCode($uCode) {
		$out = false;
		//
		// Cleaning UCode.
		$uCode = strtolower($uCode);
		//
		// Enforcing loading.
		$this->loadManifests();
		//
		// Looking for the right one.
		foreach($this->_manifests as $manifest) {
			if($manifest->information()->ucode == $uCode) {
				$out = $manifest;
				break;
			}
		}

		return $out;
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

					$url = '';
					if($info->icon) {
						$url = Paths::Instance()->imagePath($info->icon, 'png');
					} else {
						$url = Paths::Instance()->imagePath('TooBasic-default-module-icon-512px', 'png');
					}
					$uri = Paths::Path2Uri($url);

					echo '<div class="panel panel-default">';
					echo "<div class=\"panel-heading\"><img src=\"{$uri}\" class=\"pull-right\"style=\"width:20px;height:auto;\"/> {$info->name} (v{$info->version})</div>";
					echo '<div class="panel-body">';
					echo '<table class="table">';

					echo '<tr>';
					echo '<td colspan="2"><center>';
					echo '<img src="'.$uri.'" style="width:64px;height:auto;"/>';
					echo '</center></td>';
					echo '</tr>';

					echo '<tr>';
					echo '<td><strong>UCode</strong>:</td>';
					echo "<td>{$info->ucode}</td>";
					echo '</tr>';

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

					foreach($info->required_versions as $field => $reqVersion) {
						$matches = false;
						if(preg_match('/^mod:(?P<ucode>.+)$/', $field, $matches)) {
							$manifest = ManifestsManager::Instance()->manifestByUCode($matches['ucode']);
							$dependencyName = $manifest ? $manifest->information()->name : "<Unknown-{$field}>";
							echo "<li><strong>{$dependencyName}</strong> <sup>ucode:{$matches['ucode']}</sup>: {$reqVersion}</li>";
						}
					}

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
			$this->_manifests = [];
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
		$this->_errors[] = [
			GC_AFIELD_CODE => $code,
			GC_AFIELD_MESSAGE => $message,
			GC_AFIELD_MODULE_NAME => $module
		];
	}
}
