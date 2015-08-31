<?php

/**
 * @file ControllerExports.php
 * @author Alejandro Dario Simi
 */

namespace TooBasic;

/**
 * @class ControllerExports
 *
 * Because giving access to a controller's methods inside a view is a security
 * issue, this proxy class export only the required methods.
 */
class ControllerExports extends AbstractExports {
	//
	// Protected methods.
	protected $_htmlAssets = false;
	//
	// Public methods.
	public function ajaxInsert($actionName, $params = array(), $attrs = array()) {
		//
		// Enforcing parameters.
		if(!is_array($params)) {
			$params = array();
		}
		if(!is_array($attrs)) {
			$attrs = array();
		}
		//
		// Generation action uri.
		$actionUri = '?'.GC_REQUEST_ACTION."={$actionName}";
		foreach($params as $k => $v) {
			$actionUri.= "&{$k}={$v}";
		}
		//
		// Enrouting uri.
		$auxActionUri = \TooBasic\Managers\RoutesManager::Instance()->enroute($actionUri);
		if($auxActionUri != $actionUri) {
			$actionUri = ROOTURI.$auxActionUri;
		}
		//
		// Generating attributes.
		$builtAttrs = '';
		foreach($attrs as $k => $v) {
			$builtAttrs .=" {$k}=\"{$v}\"";
		}
		//
		// Creating a HTML snippet.
		$code = "<div{$builtAttrs} data-toobasic-insert=\"{$actionUri}\"></div>\n";
		//
		// Returning the generated code.
		return $code;
	}
	/**
	 * It takes an action name an returns its rendered result.
	 * 
	 * @param string $actionName Action to be rendered.
	 * @return string Rendered result.
	 */
	public function insert($actionName) {
		return $this->_controller->insert($actionName);
	}
	/**
	 * This method insert a list of configured javascipts as 'script' tags.
	 *
	 * @param string $specific Name of the specific configuration of assets.
	 * When TRUE uses the controllers names. When FALSE or if the specific
	 * configuration is not present, it uses the default configuration of
	 * assets.
	 * @param string $spacer String to prepend on each code-line generated.
	 * @return string Returns a generated code to insert.
	 */
	public function htmlAllScripts($specific = false, $spacer = '') {
		//
		// Default values.
		$code = '';
		$nothing = true;
		//
		// Loading HTML assets confgurations.
		$htmlAssets = $this->getHtmlConfigs($specific);
		//
		// Opening a comment to identify assets insertion.
		if($specific) {
			$code .="{$spacer}<!-- Scripts for '{$htmlAssets[GC_AFIELD_NAME]}' @{ -->\n";
		} else {
			$code .="{$spacer}<!-- Scripts @{ -->\n";
		}
		//
		// Creating a HTML snippet with all configured paths.
		foreach($htmlAssets[GC_DEFAULTS_HTMLASSETS_SCRIPTS] as $assetName) {
			$matches = false;
			//
			// When a asset starts with 'lib:' it must be looked for
			// in 'ROOTDIR/libraries/', otherwise it would be in
			// '.../scripts/'.
			if(preg_match('/lib:(?<path>.*)/', $assetName, $matches)) {
				$assetUri = $this->lib($matches['path']);
			} else {
				$assetUri = $this->js($assetName);
			}
			//
			// Generating the inclution piece of code.
			if($assetUri) {
				$code .="{$spacer}<script type=\"text/javascript\" src=\"{$assetUri}\" data-toobasic=\"true\"></script>\n";
				$nothing = false;
			}
		}
		//
		// Closing comment.
		$code .="{$spacer}<!-- @} -->";
		//
		// Returning the generated code only if there's something to
		// include.
		return $nothing ? '' : $code;
	}
	/**
	 * This method insert a list of configured stylesheets as 'link' tags.
	 *
	 * @param string $specific Name of the specific configuration of assets.
	 * When TRUE uses the controllers names. When FALSE or if the specific
	 * configuration is not present, it uses the default configuration of
	 * assets.
	 * @param string $spacer String to prepend on each code-line generated.
	 * @return string Returns a generated code to insert.
	 */
	public function htmlAllStyles($specific = false, $spacer = '') {
		//
		// Default values.
		$code = '';
		$nothing = true;
		//
		// Loading HTML assets confgurations.
		$htmlAssets = $this->getHtmlConfigs($specific);
		//
		// Opening a comment to identify assets insertion.
		if($specific) {
			$code .="{$spacer}<!-- Styles for '{$htmlAssets[GC_AFIELD_NAME]}' @{ -->\n";
		} else {
			$code .="{$spacer}<!-- Styles @{ -->\n";
		}
		//
		// Creating a HTML snippet with all configured paths.
		foreach($htmlAssets[GC_DEFAULTS_HTMLASSETS_STYLES] as $assetName) {
			$matches = false;
			//
			// When a asset starts with 'lib:' it must be looked for
			// in 'ROOTDIR/libraries/', otherwise it would be in
			// '.../styles/'.
			if(preg_match('/lib:(?<path>.*)/', $assetName, $matches)) {
				$assetUri = $this->lib($matches['path']);
			} else {
				$assetUri = $this->css($assetName);
			}
			//
			// Generating the inclution piece of code.
			if($assetUri) {
				$code .="{$spacer}<link type=\"text/css\" rel=\"stylesheet\" href=\"{$assetUri}\" data-toobasic=\"true\"/>\n";
				$nothing = false;
			}
		}
		//
		// Closing comment.
		$code .="{$spacer}<!-- @} -->";
		//
		// Returning the generated code only if there's something to
		// include.
		return $nothing ? '' : $code;
	}
	/**
	 * It takes an snippet name an returns its rendered result.
	 * 
	 * @param string $snippetName Name of the snippet to render.
	 * @param string $snippetDataSet List of assignments' name to use when
	 * rendering.
	 * @return string Result of rendering the snippet.
	 */
	public function snippet($snippetName, $snippetDataSet = false) {
		return $this->_controller->snippet($snippetName, $snippetDataSet);
	}
	//
	// Protected methods.
	/**
	 * This method gets the right configuration to use on some insertion.
	 *
	 * @param string $specific Name of the specific configuration of assets.
	 * When TRUE uses the controllers names. When FALSE or if the specific
	 * configuration is not present, it uses the default configuration of
	 * assets.
	 * @return mixed[string] Rerturns a proper configuration of assets.
	 */
	protected function getHtmlConfigs($specific) {
		//
		// Default values.
		$htmlAssets = false;
		//
		// Global dependencies.
		global $Defaults;
		//
		// Guessing the specific name.
		$specificName = false;
		if($specific === true) {
			//
			// Based on the controller.
			$specificName = $this->_controller->name();
		} elseif($specific) {
			//
			// As given.
			$specificName = $specific;
		}
		//
		// Attepting to load the specific configuration of assets,
		// otherwise it uses the default one.
		if($specificName && isset($Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS][$specificName])) {
			$htmlAssets = $Defaults[GC_DEFAULTS_HTMLASSETS_SPECIFICS][$specificName];
		} else {
			$htmlAssets = $Defaults[GC_DEFAULTS_HTMLASSETS];
		}
		//
		// Required fields.
		$enforce = array(
			GC_DEFAULTS_HTMLASSETS_SCRIPTS,
			GC_DEFAULTS_HTMLASSETS_STYLES
		);
		//
		// Enforcing fields.
		foreach($enforce as $e) {
			if(!isset($htmlAssets[$e])) {
				$htmlAssets[$e] = array();
			}
		}
		//
		// Adding the specific name used.
		$htmlAssets[GC_AFIELD_NAME] = $specificName;

		return $htmlAssets;
	}
}
